<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFieldsToTicketsTable extends Migration
{
    public function up()
    {
        // Aggiungi nuovi campi alla tabella esistente
        $fields = [
            'description' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'subject'
            ],
            'priority' => [
                'type' => 'ENUM',
                'constraint' => ['low', 'medium', 'high', 'urgent'],
                'default' => 'medium',
                'null' => false,
                'after' => 'status'
            ],
            'category' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'priority'
            ],
            'assigned_to' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'category'
            ],
            'created_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'assigned_to'
            ]
        ];

        $this->forge->addColumn('tickets', $fields);

        // Modifica il campo status per supportare nuovi valori
        $this->forge->modifyColumn('tickets', [
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['open', 'pending', 'in_progress', 'resolved', 'closed'],
                'default' => 'open',
                'null' => false
            ]
        ]);

        // Aggiungi indici per le performance
        $this->forge->addKey('priority', false, false, 'tickets');
        $this->forge->addKey('assigned_to', false, false, 'tickets');
        $this->forge->addKey('created_by', false, false, 'tickets');
    }

    public function down()
    {
        // Rimuovi i campi aggiunti
        $this->forge->dropColumn('tickets', [
            'description',
            'priority',
            'category',
            'assigned_to',
            'created_by'
        ]);

        // Ripristina il campo status originale
        $this->forge->modifyColumn('tickets', [
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['open', 'closed', 'pending'],
                'default' => 'open'
            ]
        ]);
    }
}
