<?php

namespace Kestrelbright\PhpUtils;

class BcmathCalculation
{
    private int $scale;

    private int $bcMathScale;

    public function getScale()
    : int
    {
        return $this->scale;
    }

    public function setScale(int $scale)
    : void
    {
        $this->scale = $scale;
    }

    public function getBcMathScale()
    : int
    {
        return $this->bcMathScale;
    }

    public function setBcMathScale(int $bcMathScale)
    : void
    {
        $this->bcMathScale = $bcMathScale;
    }

    private function round(string $valueToRound, ?int $scale = null) : string
    {
        if ($scale === null) {
            $scale = $this->scale;
        }

        $result = $valueToRound;

        if (strpos($valueToRound, '.') !== false) {
            if ($valueToRound[0] != '-') {
                $result = bcadd($valueToRound, '0.' . str_repeat('0', $scale) . '5', $scale);
            } else {
                $result = bcsub($valueToRound, '0.' . str_repeat('0', $scale) . '5', $scale);
            }
        }
        return $result;
    }

    public function add(?string $firstElement, ?string $secondElement, ?int $scale = null): string
    {
        $result = bcadd($firstElement, $secondElement, $this->bcMathScale);

        return $this->round($result, $scale);
    }

    public function substract(?string $firstElement, ?string $secondElement, ?int $scale = null): string
    {
        $result = bcsub($firstElement, $secondElement, $this->bcMathScale);

        return $this->round($result, $scale);
    }

    public function multiply(?string $firstElement, ?string $secondElement, ?int $scale = null): string
    {
        $result = bcmul($firstElement, $secondElement, $this->bcMathScale);

        return $this->round($result, $scale);
    }

    public function divide(?string $firstElement, ?string $secondElement, ?int $scale = null): string
    {
        $result = bcdiv($firstElement, $secondElement, $this->bcMathScale);

        return $this->round($result, $scale);
    }
}