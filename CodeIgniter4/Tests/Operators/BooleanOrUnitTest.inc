<?php

if ($a OR $b === $c) {} // Fail
if ($a === $b OR $c) {} // Fail
if ($a === $b OR $a === $c) {} // Fail
if ($a === 1 or $a === 2 OR $a === 3) {} // Fail 2 errors
