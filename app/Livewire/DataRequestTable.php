<?php

namespace App\Livewire;

use App\Models\DataRequest;
use App\Models\Client;
use App\Models\User;
use App\Models\KapProfile;
use App\Models\Invitation;
use App\Notifications\DataRequestRevisionNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

class DataRequestTable extends Component
{
    use WithFileUploads;

    public ?int $clientId = null;
    public bool $showModal = false;
    public ?int $editId = null;

    // PIC (User Audit) List
    public $availablePics = [];
    public ?int $pic_id = null;

    // Form fields
    public int $no = 0;
    public string $section_code = '';
    public string $section_no_input = '';
    public string $account_process = '';
    public string $description = '';
    public ?string $request_date = null;
    public ?string $expected_received = null;
    public string $status = 'pending';
    public string $comment_client = '';
    public string $comment_auditor = '';

    // File & comments
    public $uploadFiles = [];
    public ?int $commentRowId = null;
    public string $newComment = '';

    // File detail expansion
    public ?int $expandedFileRow = null;

    public function mount(?int $clientId = null)
    {
        $this->clientId = $clientId;

        /** @var User|null $user */
        $user = Auth::user();
        if (!$user) {
            abort(403);
        }

        // If auditi, find their client_id from invitation
        if ($user->isAuditi() && !$this->clientId) {
            $invitation = Invitation::where('email', $user->email)
                ->whereNotNull('accepted_at')
                ->where(function (Builder $q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->first();
            $this->clientId = $invitation?->client_id;
        }

        $this->authorizeClientAccess();
    }

    private function loadPics()
    {
        if ($this->clientId) {
            $invitations = Invitation::where('client_id', $this->clientId)
                ->whereNotNull('accepted_at')
                ->pluck('email');

            $this->availablePics = User::whereIn('email', $invitations)->get(['id', 'name', 'email']);
        }
    }

    public function updatedClientId(): void
    {
        $this->authorizeClientAccess();
        $this->loadPics();
    }

    public function openAddModal()
    {
        $this->authorizeClientAccess();
        $this->loadPics();

        $this->reset(['no', 'pic_id', 'section_code', 'section_no_input', 'account_process', 'description', 'request_date', 'expected_received', 'status', 'comment_client', 'comment_auditor', 'editId']);
        $this->status = 'pending';

        // Auto-increment no
        $lastNo = $this->getQuery()->max('no');
        $this->no = ($lastNo ?? 0) + 1;
        $this->request_date = date('Y-m-d');

        $this->showModal = true;
    }

    public function editRow(int $id)
    {
        $this->authorizeClientAccess();
        $this->loadPics();

        $row = $this->getQuery()->findOrFail($id);
        $this->editId = $row->id;
        $this->pic_id = $row->pic_id;
        $this->no = $row->no;
        $this->section_code = $row->section_code ?? '';
        $this->section_no_input = $row->section_no !== null ? (string) $row->section_no : '';
        $this->account_process = $row->account_process ?? '';
        $this->description = $row->description ?? '';
        $this->request_date = $row->request_date ? date('Y-m-d', strtotime((string) $row->request_date)) : null;
        $this->expected_received = $row->expected_received ? date('Y-m-d', strtotime((string) $row->expected_received)) : null;
        $this->status = $row->status;
        $this->comment_client = $row->comment_client ?? '';
        $this->comment_auditor = $row->comment_auditor ?? '';
        $this->showModal = true;
    }

    public function save()
    {
        $this->authorizeClientAccess();

        $this->validate([
            'no' => 'required|integer|min:1',
            'pic_id' => 'nullable|exists:users,id',
            'section_code' => 'nullable|max:10',
            'section_no_input' => 'nullable|string|max:20',
            'account_process' => 'nullable|max:255',
            'description' => 'nullable',
            'request_date' => 'nullable|date',
            'expected_received' => 'nullable|date',
            'status' => 'required|in:partially_received,on_review,received,not_applicable,pending',
        ]);

        $kap = $this->getKapId();

        $data = [
            'client_id' => $this->clientId,
            'kap_id' => $kap,
            'pic_id' => $this->pic_id ?: null,
            'no' => $this->no,
            'section_code' => $this->section_code ?: null,
            'section_no' => $this->section_no_input !== '' ? $this->section_no_input : null,
            'account_process' => $this->account_process,
            'description' => $this->description,
            'request_date' => $this->request_date,
            'expected_received' => $this->expected_received,
            'status' => $this->status,
            'comment_client' => $this->comment_client,
            'comment_auditor' => $this->comment_auditor,
        ];

        if ($this->editId) {
            $this->getQuery()->findOrFail($this->editId)->update($data);
            session()->flash('success', 'Data Request berhasil diperbarui!');
        } else {
            DataRequest::create($data);
            session()->flash('success', 'Data Request berhasil ditambahkan!');
        }

        $this->showModal = false;
    }

    public function deleteRow(int $id)
    {
        $this->authorizeClientAccess();
        $this->getQuery()->findOrFail($id)->delete();
        session()->flash('success', 'Data Request berhasil dihapus!');
    }

    public function toggleFileDetail(int $id)
    {
        $this->expandedFileRow = $this->expandedFileRow === $id ? null : $id;
    }

    public function uploadFilesForRow(int $id)
    {
        $this->authorizeClientAccess();

        $this->validate([
            'uploadFiles.*' => 'required|file|max:10240'
        ]);

        $row = $this->getQuery()->findOrFail($id);

        $currentInputFiles = $row->input_file ?? [];
        if (!is_array($currentInputFiles)) {
            $currentInputFiles = [];
        }

        // Format detect: if array elements aren't associative with "version", convert them first.
        $needsMigration = false;
        if (count($currentInputFiles) > 0 && !isset($currentInputFiles[0]['version'])) {
            $needsMigration = true;
        }

        if ($needsMigration) {
            $oldFiles = $currentInputFiles;
            $currentInputFiles = [
                [
                    'version' => 1,
                    'files' => $oldFiles,
                    'uploaded_at' => $row->date_input ? $row->date_input->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s'),
                    'uploaded_by' => 'Auto-migrated' // unknown (old logic)
                ]
            ];
        }

        $nextVersionNumber = count($currentInputFiles) + 1;

        $newPaths = [];
        foreach ($this->uploadFiles as $file) {
            $newPaths[] = $file->store("uploads/{$this->clientId}", 'public');
        }

        $currentInputFiles[] = [
            'version' => $nextVersionNumber,
            'files' => $newPaths,
            'uploaded_at' => now()->format('Y-m-d H:i:s'),
            'uploaded_by' => Auth::user()?->name ?? 'Unknown',
        ];

        $row->update([
            'input_file' => $currentInputFiles, // always an array of versions now
            'status' => DataRequest::STATUS_ON_REVIEW,
            'date_input' => now(), // track latest action time
        ]);

        // Trigger Notification ke Auditor -> bahwa ada unggahan revisi/dokumen baru
        if ($row->kap_id) {
            $auditors = User::where('id', KapProfile::where('id', $row->kap_id)->value('user_id'))->get();

            foreach ($auditors as $auditor) {
                // Buat notifikasi singkat menggunakan Notification facade
                Notification::send($auditor, new class($row) extends \Illuminate\Notifications\Notification {
                    public $req;
                    public function __construct($req)
                    {
                        $this->req = $req;
                    }
                    public function via($notifiable)
                    {
                        return ['database'];
                    }
                    public function toArray($notifiable)
                    {
                        return [
                            'message' => "Auditi mengunggah dokumen (v" . count($this->req->input_file) . ") pada Data Request " . $this->req->section,
                            'data_request_id' => $this->req->id
                        ];
                    }
                });
            }
        }

        $this->uploadFiles = [];
        $this->expandedFileRow = null;
        session()->flash('success', count($newPaths) . ' file berhasil diupload sebagai versi ' . $nextVersionNumber . '! Status berubah ke On Review.');
    }

    public function updateStatus(int $id, string $status)
    {
        $this->authorizeClientAccess();
        $row = $this->getQuery()->findOrFail($id);
        $row->update(['status' => $status]);
        session()->flash('success', 'Status berhasil diperbarui!');
    }

    public function requestRevision(int $id)
    {
        $this->authorizeClientAccess();
        $row = $this->getQuery()->findOrFail($id);

        if (empty(trim($row->comment_auditor))) {
            session()->flash('error', 'Komentar Auditor wajib diisi sebelum meminta revisi!');
            return;
        }

        $row->update([
            'status' => DataRequest::STATUS_PARTIALLY_RECEIVED,
        ]);

        // Trigger Notification to PIC or to all related auditis
        $auditis = collect();
        if ($row->pic_id) {
            $user = User::find($row->pic_id);
            if ($user && $user->isAuditi()) {
                $auditis->push($user);
            }
        } else {
            $invitationsEmails = Invitation::where('client_id', $this->clientId)
                ->whereNotNull('accepted_at')
                ->pluck('email');

            /** @var \Illuminate\Database\Eloquent\Collection<int, User> $auditis */
            $auditis = User::whereIn('email', $invitationsEmails)->get();
        }

        Notification::send($auditis, new DataRequestRevisionNotification($row));

        session()->flash('success', 'Permintaan revisi dikirim. Status berubah menjadi Partially Received.');
    }

    public function saveComment(int $id)
    {
        $this->authorizeClientAccess();
        $row = $this->getQuery()->findOrFail($id);
        /** @var User|null $user */
        $user = Auth::user();
        if (!$user) {
            abort(403);
        }

        $field = $user->isAuditor() ? 'comment_auditor' : 'comment_client';
        $row->update([$field => $this->newComment]);
        $this->commentRowId = null;
        $this->newComment = '';
    }

    public function openComment(int $id)
    {
        $this->authorizeClientAccess();
        $row = $this->getQuery()->findOrFail($id);
        $this->commentRowId = $id;
        /** @var User|null $user */
        $user = Auth::user();
        if (!$user) {
            abort(403);
        }

        $this->newComment = $user->isAuditor()
            ? ($row->comment_auditor ?? '')
            : ($row->comment_client ?? '');
    }

    private function getQuery()
    {
        return DataRequest::query()->where('client_id', $this->clientId);
    }

    private function authorizeClientAccess(): void
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (!$user || !$this->clientId) {
            return;
        }

        if ($user->isAuditor()) {
            $kapId = $user->kapProfile?->id;
            if (!$kapId) {
                abort(403);
            }

            $isOwned = Client::where('id', $this->clientId)
                ->where('kap_id', $kapId)
                ->exists();

            if (!$isOwned) {
                abort(403);
            }

            return;
        }

        if ($user->isAuditi()) {
            $allowed = Invitation::where('email', $user->email)
                ->whereNotNull('accepted_at')
                ->where(function (Builder $q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->where('client_id', $this->clientId)
                ->exists();

            if (!$allowed) {
                abort(403);
            }
        }
    }

    private function getKapId()
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (!$user) {
            return null;
        }

        if ($user->isAuditor()) {
            return $user->kapProfile?->id;
        }
        $invitation = Invitation::where('email', $user->email)
            ->whereNotNull('accepted_at')
            ->first();
        return $invitation?->kap_id;
    }

    public function exportCsv()
    {
        $this->authorizeClientAccess();
        $requests = $this->getQuery()->get();

        $filename = 'data-requests-client-' . $this->clientId . '-' . date('YmdHis') . '.csv';
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        /* @var \Illuminate\Support\Collection $requests */
        $callback = function () use ($requests) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['No', 'Section', 'Account/Process', 'Description', 'Request Date', 'Expected Received', 'Status', 'Date Input', 'Comment Client', 'Comment Auditor']);

            foreach ($requests as $row) {
                fputcsv($file, [
                    $row->no,
                    $row->section,
                    $row->account_process,
                    strip_tags($row->description),
                    $row->request_date ? $row->request_date->format('Y-m-d') : '',
                    $row->expected_received ? $row->expected_received->format('Y-m-d') : '',
                    $row->status,
                    $row->date_input ? $row->date_input->format('Y-m-d H:i') : '',
                    $row->comment_client,
                    $row->comment_auditor
                ]);
            }

            fclose($file);
        };

        return response()->streamDownload($callback, $filename, $headers);
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $this->authorizeClientAccess();

        $requests = $this->clientId
            ? $this->getQuery()
            ->orderBy('section_code')
            ->orderBy('section_no')
            ->orderBy('no')
            ->get()
            : collect();

        $clients = [];
        /** @var User|null $user */
        $user = Auth::user();
        if ($user && $user->isAuditor() && $user->kapProfile) {
            $clients = $user->kapProfile->clients;
        }

        return view('livewire.data-request-table', [
            'requests' => $requests,
            'clients' => $clients,
            'statuses' => DataRequest::STATUSES,
        ]);
    }
}
