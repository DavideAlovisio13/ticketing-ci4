<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * --------------------------------------------------------------------
 * Migrazione: creazione tabella `tickets`
 * --------------------------------------------------------------------
 * Definisce lo schema di base per il sistema di gestione ticket.
 * NB: i commenti non alterano la logica della migrazione.
 */
class CreateTicketsTable extends Migration
{
    /**
     * Eseguito in `php spark migrate`.
     * Crea la tabella con campi, chiavi e opzioni.
     */
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,   // PK autoincrementale
            ],
            'subject' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,        // Oggetto/titolo del ticket
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['open', 'closed', 'pending'],
                'default'    => 'open',     // Stato iniziale
            ],
            // Timestamps gestiti (opz.) da Model::useTimestamps
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        // Imposta `id` come primary key
        $this->forge->addKey('id', true);

        // Crea la tabella `tickets`
        $this->forge->createTable('tickets');
    }

    /**
     * Eseguito in `php spark migrate:rollback`.
     * Elimina la tabella.
     */
    public function down()
    {
        $this->forge->dropTable('tickets');
    }
}
