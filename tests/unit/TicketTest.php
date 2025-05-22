<?php

namespace Tests\Unit;

use App\Models\TicketModel;
use App\Services\TicketService;
use App\Exceptions\TicketException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

class TicketTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate = true;
    protected $migrateOnce = false;
    protected $refresh = true;
    protected $namespace = 'Tests\Support';

    protected TicketModel $ticketModel;
    protected TicketService $ticketService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ticketModel = new TicketModel();
        $this->ticketService = new TicketService();
    }

    public function testTicketModelConstants()
    {
        $this->assertEquals('open', TicketModel::STATUS_OPEN);
        $this->assertEquals('closed', TicketModel::STATUS_CLOSED);
        $this->assertEquals('low', TicketModel::PRIORITY_LOW);
        $this->assertEquals('urgent', TicketModel::PRIORITY_URGENT);
    }

    public function testGetAvailableStatuses()
    {
        $statuses = TicketModel::getAvailableStatuses();

        $this->assertIsArray($statuses);
        $this->assertArrayHasKey('open', $statuses);
        $this->assertArrayHasKey('closed', $statuses);
        $this->assertEquals('Open', $statuses['open']);
    }

    public function testGetAvailablePriorities()
    {
        $priorities = TicketModel::getAvailablePriorities();

        $this->assertIsArray($priorities);
        $this->assertArrayHasKey('low', $priorities);
        $this->assertArrayHasKey('urgent', $priorities);
        $this->assertEquals('Low', $priorities['low']);
    }

    public function testCreateTicketValidData()
    {
        $data = [
            'subject' => 'Test ticket subject',
            'description' => 'Test description',
            'status' => 'open',
            'priority' => 'medium'
        ];

        $ticket = $this->ticketService->createTicket($data);

        $this->assertIsArray($ticket);
        $this->assertEquals($data['subject'], $ticket['subject']);
        $this->assertEquals($data['status'], $ticket['status']);
        $this->assertEquals($data['priority'], $ticket['priority']);
    }

    public function testCreateTicketWithInvalidStatus()
    {
        $this->expectException(TicketException::class);

        $data = [
            'subject' => 'Test ticket',
            'status' => 'invalid_status',
            'priority' => 'medium'
        ];

        $this->ticketService->createTicket($data);
    }

    public function testCreateTicketWithInvalidPriority()
    {
        $this->expectException(TicketException::class);

        $data = [
            'subject' => 'Test ticket',
            'status' => 'open',
            'priority' => 'invalid_priority'
        ];

        $this->ticketService->createTicket($data);
    }

    public function testCreateTicketWithShortSubject()
    {
        $this->expectException(TicketException::class);

        $data = [
            'subject' => 'Hi',  // Too short
            'status' => 'open',
            'priority' => 'medium'
        ];

        $this->ticketService->createTicket($data);
    }

    public function testGetTicketById()
    {
        // Create a ticket first
        $data = [
            'subject' => 'Test ticket for retrieval',
            'status' => 'open',
            'priority' => 'high'
        ];

        $createdTicket = $this->ticketService->createTicket($data);

        // Retrieve the ticket
        $retrievedTicket = $this->ticketService->getTicketById($createdTicket['id']);

        $this->assertEquals($createdTicket['id'], $retrievedTicket['id']);
        $this->assertEquals($data['subject'], $retrievedTicket['subject']);
    }

    public function testGetTicketByIdNotFound()
    {
        $this->expectException(TicketException::class);
        $this->expectExceptionCode(404);

        $this->ticketService->getTicketById(999999);
    }

    public function testUpdateTicket()
    {
        // Create a ticket first
        $data = [
            'subject' => 'Original subject',
            'status' => 'open',
            'priority' => 'medium'
        ];

        $ticket = $this->ticketService->createTicket($data);

        // Update the ticket
        $updateData = [
            'subject' => 'Updated subject',
            'priority' => 'high'
        ];

        $updatedTicket = $this->ticketService->updateTicket($ticket['id'], $updateData);

        $this->assertEquals($updateData['subject'], $updatedTicket['subject']);
        $this->assertEquals($updateData['priority'], $updatedTicket['priority']);
        $this->assertEquals('open', $updatedTicket['status']); // Should remain unchanged
    }

    public function testDeleteTicket()
    {
        // Create a ticket first
        $data = [
            'subject' => 'Ticket to delete',
            'status' => 'open',
            'priority' => 'low'
        ];

        $ticket = $this->ticketService->createTicket($data);

        // Delete the ticket
        $result = $this->ticketService->deleteTicket($ticket['id']);

        $this->assertTrue($result);

        // Verify it's deleted
        $this->expectException(TicketException::class);
        $this->ticketService->getTicketById($ticket['id']);
    }

    public function testCloseTicket()
    {
        // Create an open ticket
        $data = [
            'subject' => 'Ticket to close',
            'status' => 'open',
            'priority' => 'medium'
        ];

        $ticket = $this->ticketService->createTicket($data);

        // Close the ticket
        $closedTicket = $this->ticketService->closeTicket($ticket['id']);

        $this->assertEquals('closed', $closedTicket['status']);
    }

    public function testCloseAlreadyClosedTicket()
    {
        // Create a closed ticket
        $data = [
            'subject' => 'Already closed ticket',
            'status' => 'closed',
            'priority' => 'medium'
        ];

        $ticket = $this->ticketService->createTicket($data);

        // Try to close it again
        $this->expectException(TicketException::class);
        $this->expectExceptionCode(400);

        $this->ticketService->closeTicket($ticket['id']);
    }

    public function testReopenTicket()
    {
        // Create a closed ticket
        $data = [
            'subject' => 'Ticket to reopen',
            'status' => 'closed',
            'priority' => 'medium'
        ];

        $ticket = $this->ticketService->createTicket($data);

        // Reopen the ticket
        $reopenedTicket = $this->ticketService->reopenTicket($ticket['id']);

        $this->assertEquals('open', $reopenedTicket['status']);
    }

    public function testReopenNonClosedTicket()
    {
        // Create an open ticket
        $data = [
            'subject' => 'Open ticket',
            'status' => 'open',
            'priority' => 'medium'
        ];

        $ticket = $this->ticketService->createTicket($data);

        // Try to reopen it
        $this->expectException(TicketException::class);
        $this->expectExceptionCode(400);

        $this->ticketService->reopenTicket($ticket['id']);
    }

    public function testAssignTicket()
    {
        // Create a ticket
        $data = [
            'subject' => 'Ticket to assign',
            'status' => 'open',
            'priority' => 'medium'
        ];

        $ticket = $this->ticketService->createTicket($data);

        // Assign the ticket
        $assignedTicket = $this->ticketService->assignTicket($ticket['id'], 123);

        $this->assertEquals(123, $assignedTicket['assigned_to']);
        $this->assertEquals('in_progress', $assignedTicket['status']);
    }

    public function testGetAllTicketsWithPagination()
    {
        // Create multiple tickets
        for ($i = 1; $i <= 15; $i++) {
            $this->ticketService->createTicket([
                'subject' => "Test ticket $i",
                'status' => 'open',
                'priority' => 'medium'
            ]);
        }

        // Get first page
        $result = $this->ticketService->getAllTickets(['page' => 1, 'per_page' => 10]);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertArrayHasKey('page', $result);
        $this->assertArrayHasKey('per_page', $result);
        $this->assertArrayHasKey('total_pages', $result);

        $this->assertCount(10, $result['data']);
        $this->assertEquals(15, $result['total']);
        $this->assertEquals(2, $result['total_pages']);
    }

    public function testGetAllTicketsWithFilters()
    {
        // Create tickets with different statuses
        $this->ticketService->createTicket([
            'subject' => 'Open ticket',
            'status' => 'open',
            'priority' => 'high'
        ]);

        $this->ticketService->createTicket([
            'subject' => 'Closed ticket',
            'status' => 'closed',
            'priority' => 'low'
        ]);

        // Filter by status
        $result = $this->ticketService->getAllTickets(['status' => 'open']);

        $this->assertCount(1, $result['data']);
        $this->assertEquals('open', $result['data'][0]['status']);

        // Filter by priority
        $result = $this->ticketService->getAllTickets(['priority' => 'high']);

        $this->assertCount(1, $result['data']);
        $this->assertEquals('high', $result['data'][0]['priority']);
    }

    public function testGetDashboardStats()
    {
        // Create tickets with different statuses and priorities
        $this->ticketService->createTicket(['subject' => 'Open ticket', 'status' => 'open', 'priority' => 'high']);
        $this->ticketService->createTicket(['subject' => 'Closed ticket', 'status' => 'closed', 'priority' => 'low']);
        $this->ticketService->createTicket(['subject' => 'Pending ticket', 'status' => 'pending', 'priority' => 'medium']);

        $stats = $this->ticketService->getDashboardStats();

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('by_status', $stats);
        $this->assertArrayHasKey('by_priority', $stats);
        $this->assertArrayHasKey('total', $stats);

        $this->assertEquals(1, $stats['by_status']['open']);
        $this->assertEquals(1, $stats['by_status']['closed']);
        $this->assertEquals(1, $stats['by_status']['pending']);
        $this->assertEquals(3, $stats['total']);
    }
}
