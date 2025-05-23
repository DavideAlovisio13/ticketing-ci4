<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * --------------------------------------------------------------------
 * Migrazione: aggiunta di campi alla tabella `tickets`
 * --------------------------------------------------------------------
 * Introduce descrizione, priorità, categoria, riferimenti utente e
 * aggiorna lo ENUM `status`. Vengono creati anche indici utili alle
 * query di filtro/ordinamento.
 */
class AddFieldsToTicketsTable extends Migration
{
    /**
     * Eseguito con `php spark migrate`.
     */
    public function up()
    {
        // -----------------------------------------------------------
        // 1) Nuovi campi da aggiungere
        // -----------------------------------------------------------
        $fields = [
            'description' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'subject', // Posiziona subito dopo `subject`
            ],
            'priority' => [
                'type' => 'ENUM',
                'constraint' => ['low', 'medium', 'high', 'urgent'],
                'default' => 'medium',
                'null' => false,
                'after' => 'status',
            ],
            'category' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'priority',
            ],
            'assigned_to' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'category',
            ],
            'created_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'assigned_to',
            ],
        ];

        // Aggiunta dei campi alla tabella esistente
        $this->forge->addColumn('tickets', $fields);

        // -----------------------------------------------------------
        // 2) Aggiorna ENUM `status` per includere stati aggiuntivi
        // -----------------------------------------------------------
        $this->forge->modifyColumn('tickets', [
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['open', 'pending', 'in_progress', 'resolved', 'closed'],
                'default' => 'open',
                'null' => false,
            ],
        ]);

        // -----------------------------------------------------------
        // 3) Indici di supporto (migliorano SELECT/WHERE/ORDER BY)
        // -----------------------------------------------------------
        $this->forge->addKey('priority',    false, false, 'tickets'); // non-unique
        $this->forge->addKey('assigned_to', false, false, 'tickets');
        $this->forge->addKey('created_by',  false, false, 'tickets');
    }

    /**
     * Eseguito con `php spark migrate:rollback`.
     * Annulla le modifiche riportando lo schema allo stato precedente.
     */
    public function down()
    {
        // 1) Rimuove i campi introdotti in `up()`
        $this->forge->dropColumn('tickets', [
            'description',
            'priority',
            'category',
            'assigned_to',
            'created_by',
        ]);

        // 2) Ripristina ENUM `status` originario
        $this->forge->modifyColumn('tickets', [
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['open', 'closed', 'pending'],
                'default' => 'open',
            ],
        ]);
    }
}
