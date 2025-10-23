<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * This migration fixes dangerous CASCADE delete constraints to prevent accidental data loss.
     *
     * Strategy:
     * - User references (submitted_by, assessed_by, etc.) -> SET NULL (preserve historical data)
     * - Instansi references -> RESTRICT (prevent deletion of institutions with data)
     * - Performance data references -> RESTRICT (protect core performance data)
     * - Permission/Role references -> CASCADE (keep as is - these should cascade)
     */
    public function up(): void
    {
        // First, make user reference columns nullable so we can use SET NULL
        DB::statement(
            "ALTER TABLE assessments MODIFY assessed_by CHAR(36) NULL",
        );
        DB::statement("ALTER TABLE reports MODIFY generated_by CHAR(36) NULL");

        // 1. Fix performance_data constraints
        // instansi_id: RESTRICT to prevent accidental deletion of institutions with data
        DB::statement(
            "ALTER TABLE performance_data DROP FOREIGN KEY performance_data_instansi_id_foreign",
        );
        DB::statement('ALTER TABLE performance_data ADD CONSTRAINT performance_data_instansi_id_foreign
            FOREIGN KEY (instansi_id) REFERENCES instansis(id) ON DELETE RESTRICT ON UPDATE CASCADE');

        // performance_indicator_id: RESTRICT to protect core indicators
        DB::statement(
            "ALTER TABLE performance_data DROP FOREIGN KEY performance_data_performance_indicator_id_foreign",
        );
        DB::statement('ALTER TABLE performance_data ADD CONSTRAINT performance_data_performance_indicator_id_foreign
            FOREIGN KEY (performance_indicator_id) REFERENCES performance_indicators(id) ON DELETE RESTRICT ON UPDATE CASCADE');

        // 2. Fix performance_indicators constraints
        // instansi_id: RESTRICT
        DB::statement(
            "ALTER TABLE performance_indicators DROP FOREIGN KEY performance_indicators_instansi_id_foreign",
        );
        DB::statement('ALTER TABLE performance_indicators ADD CONSTRAINT performance_indicators_instansi_id_foreign
            FOREIGN KEY (instansi_id) REFERENCES instansis(id) ON DELETE RESTRICT ON UPDATE CASCADE');

        // 3. Fix programs constraints
        // instansi_id: RESTRICT
        DB::statement(
            "ALTER TABLE programs DROP FOREIGN KEY programs_instansi_id_foreign",
        );
        DB::statement('ALTER TABLE programs ADD CONSTRAINT programs_instansi_id_foreign
            FOREIGN KEY (instansi_id) REFERENCES instansis(id) ON DELETE RESTRICT ON UPDATE CASCADE');

        // 4. Fix kegiatans constraints
        // program_id: RESTRICT (programs are critical, shouldn't cascade delete)
        DB::statement(
            "ALTER TABLE kegiatans DROP FOREIGN KEY kegiatans_program_id_foreign",
        );
        DB::statement('ALTER TABLE kegiatans ADD CONSTRAINT kegiatans_program_id_foreign
            FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE RESTRICT ON UPDATE CASCADE');

        // 5. Fix indikator_kinerjas constraints
        // kegiatan_id: RESTRICT
        DB::statement(
            "ALTER TABLE indikator_kinerjas DROP FOREIGN KEY indikator_kinerjas_kegiatan_id_foreign",
        );
        DB::statement('ALTER TABLE indikator_kinerjas ADD CONSTRAINT indikator_kinerjas_kegiatan_id_foreign
            FOREIGN KEY (kegiatan_id) REFERENCES kegiatans(id) ON DELETE RESTRICT ON UPDATE CASCADE');

        // 6. Fix laporan_kinerjas constraints
        // indikator_kinerja_id: RESTRICT
        DB::statement(
            "ALTER TABLE laporan_kinerjas DROP FOREIGN KEY laporan_kinerjas_indikator_kinerja_id_foreign",
        );
        DB::statement('ALTER TABLE laporan_kinerjas ADD CONSTRAINT laporan_kinerjas_indikator_kinerja_id_foreign
            FOREIGN KEY (indikator_kinerja_id) REFERENCES indikator_kinerjas(id) ON DELETE RESTRICT ON UPDATE CASCADE');

        // 7. Fix assessments constraints
        // Note: assessed_by doesn't have a foreign key constraint in the schema, skip it

        // performance_data_id: RESTRICT (assessments should stay with performance data)
        DB::statement(
            "ALTER TABLE assessments DROP FOREIGN KEY assessments_performance_data_id_foreign",
        );
        DB::statement('ALTER TABLE assessments ADD CONSTRAINT assessments_performance_data_id_foreign
            FOREIGN KEY (performance_data_id) REFERENCES performance_data(id) ON DELETE RESTRICT ON UPDATE CASCADE');

        // 8. Fix assessment_criteria constraints
        // assessment_id: CASCADE is OK here (criteria are tightly coupled to assessments)
        // Keep as is

        // 9. Fix evidence_documents constraints
        // performance_data_id: RESTRICT (evidence should stay with data)
        DB::statement(
            "ALTER TABLE evidence_documents DROP FOREIGN KEY evidence_documents_performance_data_id_foreign",
        );
        DB::statement('ALTER TABLE evidence_documents ADD CONSTRAINT evidence_documents_performance_data_id_foreign
            FOREIGN KEY (performance_data_id) REFERENCES performance_data(id) ON DELETE RESTRICT ON UPDATE CASCADE');

        // 10. Fix reports constraints
        // generated_by: SET NULL (preserve reports even if user is deleted)
        DB::statement(
            "ALTER TABLE reports DROP FOREIGN KEY reports_generated_by_foreign",
        );
        DB::statement('ALTER TABLE reports ADD CONSTRAINT reports_generated_by_foreign
            FOREIGN KEY (generated_by) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE');

        // instansi_id: RESTRICT
        DB::statement(
            "ALTER TABLE reports DROP FOREIGN KEY reports_instansi_id_foreign",
        );
        DB::statement('ALTER TABLE reports ADD CONSTRAINT reports_instansi_id_foreign
            FOREIGN KEY (instansi_id) REFERENCES instansis(id) ON DELETE RESTRICT ON UPDATE CASCADE');

        // 11. Fix targets constraints
        // performance_indicator_id: RESTRICT
        DB::statement(
            "ALTER TABLE targets DROP FOREIGN KEY targets_performance_indicator_id_foreign",
        );
        DB::statement('ALTER TABLE targets ADD CONSTRAINT targets_performance_indicator_id_foreign
            FOREIGN KEY (performance_indicator_id) REFERENCES performance_indicators(id) ON DELETE RESTRICT ON UPDATE CASCADE');

        // Note: The following are kept as CASCADE (intentional):
        // - model_has_permissions, model_has_roles, role_has_permissions: These should cascade
        // - sakip_audit_trails: Audit trails should cascade with audit_logs
        // - telescope_entries_tags: Telescope tags should cascade with entries
    }

    /**
     * Reverse the migrations.
     *
     * WARNING: Reverting to CASCADE is dangerous! Only do this if absolutely necessary.
     */
    public function down(): void
    {
        // Revert to CASCADE (NOT RECOMMENDED)

        // First revert the nullable changes
        DB::statement(
            "ALTER TABLE assessments MODIFY assessed_by CHAR(36) NOT NULL",
        );
        DB::statement(
            "ALTER TABLE reports MODIFY generated_by CHAR(36) NOT NULL",
        );

        DB::statement(
            "ALTER TABLE performance_data DROP FOREIGN KEY performance_data_instansi_id_foreign",
        );
        DB::statement('ALTER TABLE performance_data ADD CONSTRAINT performance_data_instansi_id_foreign
            FOREIGN KEY (instansi_id) REFERENCES instansis(id) ON DELETE CASCADE ON UPDATE CASCADE');

        DB::statement(
            "ALTER TABLE performance_data DROP FOREIGN KEY performance_data_performance_indicator_id_foreign",
        );
        DB::statement('ALTER TABLE performance_data ADD CONSTRAINT performance_data_performance_indicator_id_foreign
            FOREIGN KEY (performance_indicator_id) REFERENCES performance_indicators(id) ON DELETE CASCADE ON UPDATE CASCADE');

        DB::statement(
            "ALTER TABLE performance_indicators DROP FOREIGN KEY performance_indicators_instansi_id_foreign",
        );
        DB::statement('ALTER TABLE performance_indicators ADD CONSTRAINT performance_indicators_instansi_id_foreign
            FOREIGN KEY (instansi_id) REFERENCES instansis(id) ON DELETE CASCADE ON UPDATE CASCADE');

        DB::statement(
            "ALTER TABLE programs DROP FOREIGN KEY programs_instansi_id_foreign",
        );
        DB::statement('ALTER TABLE programs ADD CONSTRAINT programs_instansi_id_foreign
            FOREIGN KEY (instansi_id) REFERENCES instansis(id) ON DELETE CASCADE ON UPDATE CASCADE');

        DB::statement(
            "ALTER TABLE kegiatans DROP FOREIGN KEY kegiatans_program_id_foreign",
        );
        DB::statement('ALTER TABLE kegiatans ADD CONSTRAINT kegiatans_program_id_foreign
            FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE ON UPDATE CASCADE');

        DB::statement(
            "ALTER TABLE indikator_kinerjas DROP FOREIGN KEY indikator_kinerjas_kegiatan_id_foreign",
        );
        DB::statement('ALTER TABLE indikator_kinerjas ADD CONSTRAINT indikator_kinerjas_kegiatan_id_foreign
            FOREIGN KEY (kegiatan_id) REFERENCES kegiatans(id) ON DELETE CASCADE ON UPDATE CASCADE');

        DB::statement(
            "ALTER TABLE laporan_kinerjas DROP FOREIGN KEY laporan_kinerjas_indikator_kinerja_id_foreign",
        );
        DB::statement('ALTER TABLE laporan_kinerjas ADD CONSTRAINT laporan_kinerjas_indikator_kinerja_id_foreign
            FOREIGN KEY (indikator_kinerja_id) REFERENCES indikator_kinerjas(id) ON DELETE CASCADE ON UPDATE CASCADE');

        // Note: assessed_by doesn't have a foreign key constraint, skip it

        DB::statement(
            "ALTER TABLE assessments DROP FOREIGN KEY assessments_performance_data_id_foreign",
        );
        DB::statement('ALTER TABLE assessments ADD CONSTRAINT assessments_performance_data_id_foreign
            FOREIGN KEY (performance_data_id) REFERENCES performance_data(id) ON DELETE CASCADE ON UPDATE CASCADE');

        DB::statement(
            "ALTER TABLE evidence_documents DROP FOREIGN KEY evidence_documents_performance_data_id_foreign",
        );
        DB::statement('ALTER TABLE evidence_documents ADD CONSTRAINT evidence_documents_performance_data_id_foreign
            FOREIGN KEY (performance_data_id) REFERENCES performance_data(id) ON DELETE CASCADE ON UPDATE CASCADE');

        DB::statement(
            "ALTER TABLE reports DROP FOREIGN KEY reports_generated_by_foreign",
        );
        DB::statement('ALTER TABLE reports ADD CONSTRAINT reports_generated_by_foreign
            FOREIGN KEY (generated_by) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE');

        DB::statement(
            "ALTER TABLE reports DROP FOREIGN KEY reports_instansi_id_foreign",
        );
        DB::statement('ALTER TABLE reports ADD CONSTRAINT reports_instansi_id_foreign
            FOREIGN KEY (instansi_id) REFERENCES instansis(id) ON DELETE CASCADE ON UPDATE CASCADE');

        DB::statement(
            "ALTER TABLE targets DROP FOREIGN KEY targets_performance_indicator_id_foreign",
        );
        DB::statement('ALTER TABLE targets ADD CONSTRAINT targets_performance_indicator_id_foreign
            FOREIGN KEY (performance_indicator_id) REFERENCES performance_indicators(id) ON DELETE CASCADE ON UPDATE CASCADE');
    }
};
