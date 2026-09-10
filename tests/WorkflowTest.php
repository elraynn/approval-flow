<?php

namespace Elrayn\ApprovalFlow\Tests;

use Elrayn\ApprovalFlow\Workflow;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class WorkflowTest extends TestCase
{
    protected function workflow(): Workflow
    {
        return new Workflow([
            'draft'     => ['submitted' => 'staff'],
            'submitted' => ['confirmed' => 'panitia', 'rejected' => 'panitia'],
            'confirmed' => ['accepted'  => ['industri', 'admin']],
        ]);
    }

    public function testCanReturnsTrueForAllowedTransition(): void
    {
        $this->assertTrue($this->workflow()->can('draft', 'submitted', 'staff'));
    }

    public function testCanReturnsFalseForWrongRole(): void
    {
        $this->assertFalse($this->workflow()->can('draft', 'submitted', 'panitia'));
    }

    public function testCanReturnsFalseForUnknownTransition(): void
    {
        $this->assertFalse($this->workflow()->can('draft', 'accepted', 'staff'));
    }

    public function testApplyReturnsTargetStateWhenAllowed(): void
    {
        $this->assertSame('submitted', $this->workflow()->apply('draft', 'submitted', 'staff'));
    }

    public function testApplyThrowsWhenNotAllowed(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->workflow()->apply('draft', 'submitted', 'panitia');
    }

    public function testMultipleRolesCanTriggerSameTransition(): void
    {
        $workflow = $this->workflow();
        $this->assertTrue($workflow->can('confirmed', 'accepted', 'industri'));
        $this->assertTrue($workflow->can('confirmed', 'accepted', 'admin'));
        $this->assertFalse($workflow->can('confirmed', 'accepted', 'staff'));
    }

    public function testAvailableFromListsOnlyStatesTheRoleCanReach(): void
    {
        $this->assertSame(
            ['confirmed', 'rejected'],
            $this->workflow()->availableFrom('submitted', 'panitia')
        );
        $this->assertSame([], $this->workflow()->availableFrom('submitted', 'industri'));
    }
}
