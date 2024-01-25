<?php

namespace Thunk\VerbsCommands\Concerns;

use Thunk\Verbs\Event;
use Illuminate\Support\Collection;
use Thunk\VerbsCommands\Collections\ActionCollection;
use Thunk\VerbsCommands\Collections\PropertyCollection;

trait Actions
{
    public function availableActions(Collection $context): ActionCollection
    {
        return self::allActions()
            ->filter(function ($action) use ($context) {
                $event = $action::makeWithContext($context);

                dump(
                    $action, 
                    PropertyCollection::fromClass($action)->hasRequiredParams($context)
                        && $event->isAllowed()
                        && $event->isValid()
                );


                return PropertyCollection::fromClass($action)->hasRequiredParams($context)
                    && $event->isAllowed()
                    && $event->isValid();
            });
    }

    public function fireAction(string $action, $input = []): Event
    {
        $action = self::allActions()->get($action);

        return $action::fireWithArbitraryInput($input);
    }
}
