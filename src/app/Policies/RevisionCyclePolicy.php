<?php

namespace App\Policies;

class RevisionCyclePolicy extends SifakResourcePolicy
{
    protected string $resource = 'revision::cycle';
}
