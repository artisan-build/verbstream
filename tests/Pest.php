<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

pest()->extends(TestCase::class, DatabaseTransactions::class);
