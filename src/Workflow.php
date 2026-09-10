<?php

namespace Elrayn\ApprovalFlow;

use InvalidArgumentException;

class Workflow
{
    protected array $transitions;

    // $transitions = ['draft' => ['submitted' => 'staff'], 'submitted' => ['approved' => ['panitia', 'admin'], 'rejected' => 'panitia']]
    public function __construct(array $transitions)
    {
        $this->transitions = $transitions;
    }

    public function can(string $from, string $to, string $role): bool
    {
        $roles = $this->transitions[$from][$to] ?? null;

        if ($roles === null) {
            return false;
        }

        return in_array($role, (array) $roles, true);
    }

    public function apply(string $from, string $to, string $role): string
    {
        if (!$this->can($from, $to, $role)) {
            throw new InvalidArgumentException("Cannot transition from '{$from}' to '{$to}' as '{$role}'");
        }

        return $to;
    }

    // states $role is allowed to move to from $from
    public function availableFrom(string $from, string $role): array
    {
        $states = $this->transitions[$from] ?? [];

        return array_keys(array_filter($states, function ($roles) use ($role) {
            return in_array($role, (array) $roles, true);
        }));
    }
}
