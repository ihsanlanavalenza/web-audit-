<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_requests', function (Blueprint $table) {
            $table->string('section_code', 10)->nullable()->after('no');
            $table->integer('section_no')->nullable()->after('section_code');
            $table->timestamp('followup_sent_at')->nullable()->after('last_update');
        });

        // Migrate existing section data — try to split e.g. "A1" into code="A", no=1
        DB::table('data_requests')->whereNotNull('section')->where('section', '!=', '')->orderBy('id')->each(function ($row) {
            $section = $row->section;
            if (preg_match('/^([A-Za-z]+)(\d+)$/', $section, $matches)) {
                DB::table('data_requests')->where('id', $row->id)->update([
                    'section_code' => strtoupper($matches[1]),
                    'section_no' => (int) $matches[2],
                ]);
            } else {
                DB::table('data_requests')->where('id', $row->id)->update([
                    'section_code' => $section,
                ]);
            }
        });

        Schema::table('data_requests', function (Blueprint $table) {
            $table->dropColumn('section');
        });

        // Change date_input from date to datetime for precise timestamp
        Schema::table('data_requests', function (Blueprint $table) {
            $table->dateTime('date_input_new')->nullable()->after('comment_auditor');
        });

        DB::table('data_requests')->whereNotNull('date_input')->orderBy('id')->each(function ($row) {
            DB::table('data_requests')->where('id', $row->id)->update([
                'date_input_new' => $row->date_input . ' 00:00:00',
            ]);
        });

        Schema::table('data_requests', function (Blueprint $table) {
            $table->dropColumn('date_input');
        });

        Schema::table('data_requests', function (Blueprint $table) {
            $table->renameColumn('date_input_new', 'date_input');
        });
    }

    public function down(): void
    {
        Schema::table('data_requests', function (Blueprint $table) {
            $table->string('section')->nullable()->after('no');
        });

        DB::table('data_requests')->orderBy('id')->each(function ($row) {
            $section = ($row->section_code ?? '') . ($row->section_no ?? '');
            DB::table('data_requests')->where('id', $row->id)->update([
                'section' => $section ?: null,
            ]);
        });

        Schema::table('data_requests', function (Blueprint $table) {
            $table->dropColumn(['section_code', 'section_no', 'followup_sent_at']);
        });

        // Revert date_input back to date
        Schema::table('data_requests', function (Blueprint $table) {
            $table->date('date_input_old')->nullable();
        });

        DB::table('data_requests')->whereNotNull('date_input')->orderBy('id')->each(function ($row) {
            DB::table('data_requests')->where('id', $row->id)->update([
                'date_input_old' => date('Y-m-d', strtotime($row->date_input)),
            ]);
        });

        Schema::table('data_requests', function (Blueprint $table) {
            $table->dropColumn('date_input');
        });

        Schema::table('data_requests', function (Blueprint $table) {
            $table->renameColumn('date_input_old', 'date_input');
        });
    }
};
