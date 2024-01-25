<?php

namespace Thunk\VerbsCommands\Concerns;

use Illuminate\Support\Collection;
use Thunk\Verbs\Support\PendingEvent;
use Thunk\VerbsCommands\Collections\PropertyCollection;
use Thunk\VerbsCommands\Exceptions\MissingInputException;
use Thunk\VerbsCommands\Exceptions\MissingPropertyException;

trait AttributeInputs
{
    public static function fireWithArbitraryInput($input)
    {
        $validInput = static::validateProperties($input);

        return static::fire(...$validInput);
    }

    public static function makeWithContext(Collection $context): PendingEvent
    {
        $valid_input_keys = PropertyCollection::fromClass(static::class)
            ->presentIn($context)
            ->map(fn ($prop) => $prop->getName());
            
        $valid_input = $context->only($valid_input_keys);

        if ($valid_input->isEmpty()) {
            return static::make()->hydrate([]);
        }

        return static::make(...$valid_input);
    }

    public static function validateUserInput($input): PropertyCollection
    {
        $props = PropertyCollection::fromClass(static::class);

        if ($missing_props = $props->input(false)->missingFrom($input)) {
            throw new MissingPropertyException(
                missing: $missing_props->toArray()
            );
        }

        if ($missing_input = $props->input()->missingFrom($input)) {
            throw new MissingInputException(
                missing: $missing_input->toArray()
            );
        }

        return $input;
    }

    public static function missingFields($input)
    {
        return collect(static::fillableNames())
            ->reject(fn ($field) => isset($input[$field]))
            ->toArray();
    }

    public function hasRequiredParams($class_name)
    {
        $props = PropertyCollection::fromClass($class_name);

        $non_inputs = $props->input(false);

        $non_inputs->each(function ($prop, $name) {
            if (! $this->has($name)) {
                throw new MissingPropertyException(
                    missing: $prop
                );
            }
        });

        return $this;
    }
}
