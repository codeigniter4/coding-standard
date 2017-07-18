<?php

if ($a === $b or $a === $c) {} // Fail
if ($a === $b || $a === $c) {} // Pass
if ($a === 1 or $a === 2 or $a === 3) {} // Fail 2 errors
