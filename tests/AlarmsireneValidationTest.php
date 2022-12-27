<?php

declare(strict_types=1);

include_once __DIR__ . '/stubs/Validator.php';

class AlarmsireneValidationTest extends TestCaseSymconValidation
{
    public function testValidateLibrary(): void
    {
        $this->validateLibrary(__DIR__ . '/..');
    }

    public function testValidateModule_Alarmsirene(): void
    {
        $this->validateModule(__DIR__ . '/../Alarmsirene');
    }

    public function testValidateModule_AlarmsireneHomematic(): void
    {
        $this->validateModule(__DIR__ . '/../AlarmsireneHomematic');
    }

    public function testValidateModule_AlarmsireneHomematicIP(): void
    {
        $this->validateModule(__DIR__ . '/../AlarmsireneHomematicIP');
    }
}