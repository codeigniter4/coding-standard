<?php

if ($a AND $b === $c) {} // Fail
if ($a === $b AND $c) {} // Fail
if ($a === $b AND $a === $c) {} // Fail
if ($a === $b && $a === $c) {} // Fail
if ($a === 1 and $a === 2 AND $a === 3) {} // Fail 2 errors
