<?php
declare(strict_types=1);

namespace IwanLuijks\PhpContracts;

use LogicException;

class Contract
{
    private string $name;

    public function __construct(string $name)
    {
        if (strpos($name, ' MUST NOT ') === false && strpos($name, ' MUST ') === false
                && strpos($name, ' SHOULD NOT ') === false && strpos($name, ' SHOULD ') === false
                && strpos($name, ' NOT HAS TO ') === false && strpos($name, ' HAS TO ') === false
                && strpos($name, ' NOT HAVE TO ') === false && strpos($name, ' HAVE TO ') === false) {
            throw new LogicException('A contract name requires a clear indication of what it needs. Include one of the'
                    . ' following keywords herefor: MUST, MUST NOT, SHOULD, SHOULD NOT, HAS TO, NOT HAS TO, HAVE TO, NOT HAVE TO.');
        }

        $this->name = $name;
    }

    /**
     * Expect a certain constrained value being returned from for example a function call.
     * @param bool $expression
     * @param string $message
     * @param mixed... $messageVars
     */
    public function expects(bool $expression, string $message = 'Got an unexpected value.', ...$messageVars): self
    {
        if ($expression !== true) {
            $traceInfoAsString = $this->gatherTraceInfoAsString();
            throw new ExpectationFailedException(vsprintf($message, $messageVars).' '.$traceInfoAsString);
        }

        return $this;
    }

    /**
     * Require a constrained value for a variable.
     * @param bool $expression
     * @param string $message
     * @param mixed... $messageVars
     */
    public function requires(bool $expression, string $message = 'Program requirement not adhered.', ...$messageVars): self
    {
        if ($expression !== true) {
            $traceInfoAsString = $this->gatherTraceInfoAsString();
            throw new RequirementFailedException(vsprintf($message, $messageVars).' '.$traceInfoAsString);
        }

        return $this;
    }

    /**
     * @return string
     */
    private function gatherTraceInfoAsString(): string
    {
        $backtrace = debug_backtrace();

        $contractCalledInFile = !empty($backtrace[1]['file']) ? basename($backtrace[1]['file']) : 'unknown';
        $contractCalledOnLine = isset($backtrace[1]['line']) ? (string) $backtrace[1]['line'] : 'unknown';

        $contractTraceString = 'contract "'.$this->name.'" of '.$contractCalledInFile.', line '.$contractCalledOnLine;

        $functionWhereInContractSet = $backtrace[2]['function'] ?? 'unknown';
        $classWhereInContractSet = $backtrace[2]['class'] ?? '';
        $lineWhereInFunctionCalled = isset($backtrace[2]['line']) ? (string) $backtrace[2]['line'] : 'unknown';
        $fileWhereInFunctionCalled = isset($backtrace[2]['file']) ? basename($backtrace[2]['file']) : 'unknown';

        if ($classWhereInContractSet) {
            $functionWhereInContractSet = 'method '.$functionWhereInContractSet.' of class '.$classWhereInContractSet;
        } else {
            $functionWhereInContractSet = 'function '.$functionWhereInContractSet;
        }

        return 'Traced to '.$contractTraceString.'. Failure caused by calling '.$functionWhereInContractSet.' at line '.$lineWhereInFunctionCalled.' in '.$fileWhereInFunctionCalled.'.';
    }
}
