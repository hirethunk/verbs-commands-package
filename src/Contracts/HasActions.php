<?php

namespace Thunk\VerbsCommands\Contracts;

use Illuminate\Support\Collection;
use Thunk\Verbs\Event;
use Thunk\VerbsCommands\Collections\ActionCollection;

interface HasActions
{
    public static function allActions(): ActionCollection;

    public function availableActions(?iterable $context = null): ActionCollection;

    public function fireAction(string $action): Event;
}
