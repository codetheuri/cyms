<?php

use yii\db\Migration;

/**
 * Class m251118_095245_cyms_tables
 */
class m251118_095245_cyms_tables extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;


        // 1. Master Tables
        $this->createTable('{{%master_shipping_lines}}', [
            'line_id' => $this->bigPrimaryKey(),
            'line_code' => $this->string(20)->notNull()->unique(),
            'line_name' => $this->string(100)->notNull(),
            'contact_email' => $this->string(100),
            'is_deleted' => $this->boolean()->notNull()->defaultValue(0),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->createTable('{{%master_container_types}}', [
            'type_id' => $this->bigPrimaryKey(),
            'iso_code' => $this->string(10)->notNull()->unique(),
            'size' => $this->integer()->notNull(),
            'type_group' => $this->string(20)->notNull(),
            'description' => $this->string(100),
            'is_deleted' => $this->boolean()->notNull()->defaultValue(0),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),

        ], $tableOptions);
        $this->createTable('{{%master_container_owners}}', [
            'owner_id' => $this->bigPrimaryKey(),
            'owner_name' => $this->string(100)->notNull(),
            'owner_contact' => $this->string(50),
            'owner_email' => $this->string(100),
            'is_deleted' => $this->boolean()->notNull()->defaultValue(0),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], $tableOptions);

        // 2. Container Visits (The Master Table)
        $this->createTable('{{%container_visits}}', [
            'visit_id' => $this->bigPrimaryKey(),

            // --- CORE IDENTIFIERS ---
            'container_number' => $this->string(20)->notNull(),
            'status' => "ENUM('GATE_IN', 'SURVEYED', 'IN_YARD', 'RELEASE_ORDER', 'GATE_OUT') NOT NULL DEFAULT 'GATE_IN'",

            'shipping_line_id' => $this->bigInteger(),
            'container_owner_id' => $this->bigInteger(), // NEW: Replaces manual text
            'container_type_id' => $this->bigInteger(),
            // --- INTERCHANGE DATA (New Fields for Reports) ---
            'shipping_agent_name' => $this->string(100),
            'shipping_line_id' => $this->bigInteger(),
            'vessel_name' => $this->string(100),
            'voyage_number' => $this->string(50),
            'bl_number' => $this->string(100),

            // --- GATE IN FIELDS ---
            'ticket_no_in' => $this->string(50),
            'date_in' => $this->date(),
            'time_in' => $this->time(),
            'vehicle_reg_no_in' => $this->string(20),
            'truck_type_in' => $this->string(20),
            'trailer_reg_no_in' => $this->string(20),
            'seal_number_in' => $this->string(50),
            'truck_owner_name_in' => $this->string(100),
            'truck_owner_contact_in' => $this->string(50),
            'driver_name_in' => $this->string(100),
            'driver_id_in' => $this->string(50),
            'yard_clerk_in' => $this->integer(),
            'arrival_photo_path' => $this->string(255),
            'comments_in' => $this->text(),

            'ticket_no_out' => $this->string(50),
            'date_out' => $this->date(),
            'time_out' => $this->time(),
            'vehicle_reg_no_out' => $this->string(20),
            'truck_type_out' => $this->string(20),
            'trailer_reg_no_out' => $this->string(20),
            'seal_number_out' => $this->string(50),
            'driver_name_out' => $this->string(100),
            'driver_id_out' => $this->string(50),
            'yard_clerk_out' => $this->integer(),
            'destination' => $this->string(100),
            // --- CALCULATED FIELDS ---
            'storage_days' => $this->integer()->defaultValue(0),

            'is_deleted' => $this->boolean()->notNull()->defaultValue(0),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'created_by' => $this->bigInteger(),

            'FOREIGN KEY ([[shipping_line_id]]) REFERENCES {{%master_shipping_lines}} ([[line_id]])' .
                $this->buildFkClause('ON DELETE SET NULL', 'ON UPDATE CASCADE'),
            'FOREIGN KEY ([[container_type_id]]) REFERENCES {{%master_container_types}} ([[type_id]])' .
                $this->buildFkClause('ON DELETE SET NULL', 'ON UPDATE CASCADE'),
            'FOREIGN KEY ([[container_owner_id]]) REFERENCES {{%master_container_owners}} ([[owner_id]])' .
                $this->buildFkClause('ON DELETE SET NULL', 'ON UPDATE CASCADE'),
        ], $tableOptions);

        $this->createIndex('idx-visits-container', '{{%container_visits}}', 'container_number');

        $this->createTable('{{%visit_documents}}', [
            'doc_id' => $this->bigPrimaryKey(),
            'visit_id' => $this->bigInteger()->notNull(),
            'doc_type' => $this->string(50),
            'file_path' => $this->string(255)->notNull(),
            'uploaded_at' => $this->integer(),
            'is_deleted' => $this->boolean()->notNull()->defaultValue(0),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'FOREIGN KEY ([[visit_id]]) REFERENCES {{%container_visits}} ([[visit_id]])' .
                $this->buildFkClause('ON DELETE CASCADE', 'ON UPDATE CASCADE'),
        ], $tableOptions);

        // 3. Yard Slots
        $this->createTable('{{%yard_slots}}', [
            'slot_id' => $this->primaryKey(),
            'block' => $this->string(10)->notNull(),
            'row' => $this->integer()->notNull(),
            'bay' => $this->integer()->notNull(),
            'tier' => $this->integer()->notNull()->defaultValue(1),
            'slot_name' => $this->string(20)->unique(),
            'current_visit_id' => $this->bigInteger()->null(),
            'is_deleted' => $this->boolean()->notNull()->defaultValue(0),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),

            'FOREIGN KEY ([[current_visit_id]]) REFERENCES {{%container_visits}} ([[visit_id]])' .
                $this->buildFkClause('ON DELETE SET NULL', 'ON UPDATE CASCADE'),
        ], $tableOptions);

        // 4. Surveys
        $this->createTable('{{%container_surveys}}', [
            'survey_id' => $this->bigPrimaryKey(),
            'visit_id' => $this->bigInteger()->notNull(),
            'survey_date' => $this->dateTime(),
            'surveyor_name' => $this->string(100),
            'approval_status' => "ENUM('PENDING', 'APPROVED', 'REJECTED') DEFAULT 'PENDING'",
            'is_deleted' => $this->boolean()->notNull()->defaultValue(0),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),

            'FOREIGN KEY ([[visit_id]]) REFERENCES {{%container_visits}} ([[visit_id]])' .
                $this->buildFkClause('ON DELETE CASCADE', 'ON UPDATE CASCADE'),
        ], $tableOptions);

        // 5. Survey Damages
        $this->createTable('{{%survey_damages}}', [
            'damage_id' => $this->bigPrimaryKey(),
            'survey_id' => $this->bigInteger()->notNull(),
            'repair_code' => $this->string(20),
            'description' => $this->string(255)->notNull(),
            'quantity' => $this->integer()->defaultValue(1),
            'labor_cost' => $this->decimal(10, 2)->defaultValue(0),
            'material_cost' => $this->decimal(10, 2)->defaultValue(0),
            'total_cost' => $this->decimal(10, 2)->defaultValue(0),
            'is_deleted' => $this->boolean()->notNull()->defaultValue(0),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),

            'FOREIGN KEY ([[survey_id]]) REFERENCES {{%container_surveys}} ([[survey_id]])' .
                $this->buildFkClause('ON DELETE CASCADE', 'ON UPDATE CASCADE'),
        ], $tableOptions);

        // 6. Billing Records (THE INVOICE)
        $this->createTable('{{%billing_records}}', [
            'bill_id' => $this->bigPrimaryKey(),
            'visit_id' => $this->bigInteger()->notNull()->unique(),
            'invoice_number' => $this->string(50),


            'storage_days' => $this->integer()->defaultValue(0),
            'tariff_rate' => $this->decimal(10, 2)->defaultValue(0),
            'storage_total' => $this->decimal(10, 2)->defaultValue(0),
            'repair_total' => $this->decimal(10, 2)->defaultValue(0),
            'lift_charges' => $this->decimal(10, 2)->defaultValue(0),


            'grand_total' => $this->decimal(10, 2)->defaultValue(0),
            'total_paid' => $this->decimal(10, 2)->defaultValue(0),
            'balance' => $this->decimal(10, 2)->defaultValue(0),
            'credit_agreement_path' => $this->string(255),
            'authorized_by' => $this->string(100),

            'status' => "ENUM('UNPAID', 'PARTIAL', 'PAID', 'CREDIT', 'CANCELLED') DEFAULT 'UNPAID'",

            'is_deleted' => $this->boolean()->notNull()->defaultValue(0),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),

            'FOREIGN KEY ([[visit_id]]) REFERENCES {{%container_visits}} ([[visit_id]])' .
                $this->buildFkClause('ON DELETE CASCADE', 'ON UPDATE CASCADE'),
        ], $tableOptions);

        // 7. Billing Payments (THE RECEIPTS - NEW TABLE)
        $this->createTable('{{%billing_payments}}', [
            'payment_id' => $this->bigPrimaryKey(),
            'bill_id' => $this->bigInteger()->notNull(),

            'amount' => $this->decimal(10, 2)->notNull(),
            'transaction_date' => $this->date()->notNull(),
            'method' => "ENUM('CASH', 'MPESA', 'BANK', 'CHEQUE', 'CREDIT') NOT NULL DEFAULT 'CASH'",
            'reference' => $this->string(100), // Mpesa Code, Cheque No, etc.

            'is_deleted' => $this->boolean()->notNull()->defaultValue(0),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),

            'FOREIGN KEY ([[bill_id]]) REFERENCES {{%billing_records}} ([[bill_id]])' .
                $this->buildFkClause('ON DELETE CASCADE', 'ON UPDATE CASCADE'),
        ], $tableOptions);
    }

    public function safeDown()
    {
        $this->dropTable('{{%visit_documents}}');
        $this->dropTable('{{%billing_payments}}');
        $this->dropTable('{{%billing_records}}');
        $this->dropTable('{{%survey_damages}}');
        $this->dropTable('{{%container_surveys}}');
        $this->dropTable('{{%yard_slots}}');
        $this->dropTable('{{%container_visits}}');
        $this->dropTable('{{%master_container_owners}}');
        $this->dropTable('{{%master_container_types}}');
        $this->dropTable('{{%master_shipping_lines}}');
    }

    protected function buildFkClause($delete = '', $update = '')
    {
        return implode(' ', ['', $delete, $update]);
    }
}
