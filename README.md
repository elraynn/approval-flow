# approval-flow

A small state machine for multi-step approval workflows: request submitted, someone confirms it, someone else accepts it, maybe it gets rejected and sent back. Define the allowed transitions and who's allowed to make them, and stop hand-rolling `if ($status == 'submitted' && $role == 'panitia')` checks in every controller.

Business apps end up with this pattern constantly: a purchase order that needs approval before it becomes an invoice, a request that needs one role to confirm and a different role to accept, a status field with 4-5 possible values and rules about who can move it from one to the next. That logic usually lives scattered across controllers as ad-hoc if/else blocks, which makes it easy for one endpoint to enforce a rule slightly differently than another.

## Install

```
composer require elrayn/approval-flow
```

## Usage

Define the graph once: for each state, which states it can move to, and which role is allowed to trigger that move.

```php
use Elrayn\ApprovalFlow\Workflow;

$workflow = new Workflow([
    'draft'     => ['submitted' => 'staff'],
    'submitted' => ['confirmed' => 'panitia', 'rejected' => 'panitia'],
    'confirmed' => ['accepted'  => 'industri', 'rejected' => 'industri'],
]);
```

Check whether a transition is allowed, and apply it:

```php
if ($workflow->can($request->status, 'confirmed', $user->role)) {
    $request->status = $workflow->apply($request->status, 'confirmed', $user->role);
    $request->save();
}
```

`apply()` throws if the transition isn't allowed, so you can skip the `can()` check and just wrap it in a try/catch if you'd rather fail loudly.

To render only the action buttons a user is actually allowed to press:

```php
$workflow->availableFrom($request->status, $user->role);
// ['confirmed', 'rejected']
```

A transition can allow more than one role:

```php
'submitted' => ['confirmed' => ['panitia', 'admin']],
```

## What it doesn't do

This only validates and returns the next state. It doesn't touch your database, doesn't log history, doesn't send notifications. Save the returned state and do whatever else your app needs around it, this is just the rulebook for which moves are legal.

## License

MIT
