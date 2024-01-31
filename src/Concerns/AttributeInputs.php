<?php

namespace Thunk\VerbsCommands\Concerns;

use Thunk\Verbs\Support\PendingEvent;
use Thunk\VerbsCommands\Collections\PropertyCollection;
use Thunk\VerbsCommands\Exceptions\MissingInputException;
use Thunk\VerbsCommands\Exceptions\MissingPropertyException;

trait AttributeInputs
{
    public static function fireWithArbitraryInput($input)
    {
        $valid_input = static::validateUserInput($input);

        return static::fire(...$valid_input);
    }

    public static function makeWithContext(iterable $context): PendingEvent
    {
        $context = collect($context);
        
        $valid_input_keys = PropertyCollection::fromClass(static::class)
            ->presentIn($context)
            ->map(fn ($prop) => $prop->getName());

        $valid_input = $context->only($valid_input_keys);

        if ($valid_input->isEmpty()) {
            return static::make()->hydrate([]);
        }

        return static::make(...$valid_input);
    }

    public static function validateUserInput(iterable $input): iterable
    {
        $props = PropertyCollection::fromClass(static::class)
            ->filter(fn ($i) => $i->getName() !== 'id');

        $missing_props = $props->input(false)->missingFrom($input);
        if ($missing_props->isNotEmpty()) {
            throw new MissingPropertyException(
                missing: $missing_props->toArray()
            );
        }

        $missing_input = $props->input()->missingFrom($input);
        if ($missing_input->isNotEmpty()) {
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
