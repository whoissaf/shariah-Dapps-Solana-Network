<?php

namespace App\Services;

use App\Models\Claim;
use App\Models\Rule;
use Illuminate\Support\Carbon;

class RuleEvaluator
{
    public function evaluate(Claim $claim): array
    {
        $rules = Rule::where('rule_type', $claim->claim_type)
            ->where('is_active', true)
            ->orderBy('position')
            ->get();

        $results = [];

        foreach ($rules as $rule) {
            $results[] = $this->evaluateRule($claim, $rule);
        }

        $eligible = collect($results)->every(function (array $result) {
            return $result['passed'];
        });

        return [
            'eligible' => $eligible,
            'results' => $results,
        ];
    }

    private function evaluateRule(Claim $claim, Rule $rule): array
    {
        $payload = $claim->payload ?? [];

        $passed = match ($rule->rule_type) {
            'income_threshold' => $this->evaluateIncome($payload, $rule),
            'age_minimum' => $this->evaluateAge($payload, $rule),
            'business_category_halal' => $this->evaluateBusinessCategory($payload, $rule),
            'no_active_restricted_financing' => $this->evaluateRestrictedFinancing($payload),
            default => false,
        };

        return [
            'rule_code' => $rule->code,
            'rule_name' => $rule->name,
            'passed' => $passed,
            'reason' => $passed ? null : $this->failureReason($rule->rule_type),
        ];
    }

    private function evaluateIncome(array $payload, Rule $rule): bool
    {
        $value = $payload['monthly_income'] ?? null;

        if (! is_numeric($value)) {
            return false;
        }

        $minimum = $rule->parameters['min_monthly_income'] ?? 0;

        return $value >= $minimum;
    }

    private function evaluateAge(array $payload, Rule $rule): bool
    {
        $value = $payload['date_of_birth'] ?? null;

        if (! is_string($value) || trim($value) === '') {
            return false;
        }

        try {
            $dateOfBirth = Carbon::parse($value);
        } catch (\Throwable $exception) {
            return false;
        }

        $minimumAge = $rule->parameters['min_age'] ?? 21;

        return $dateOfBirth->diffInYears(now()) >= $minimumAge;
    }

    private function evaluateBusinessCategory(array $payload, Rule $rule): bool
    {
        $value = strtolower((string) ($payload['business_category'] ?? ''));
        $keyword = strtolower((string) ($rule->parameters['keyword'] ?? 'halal'));

        return trim($value) !== '' && trim($keyword) !== '' && str_contains($value, $keyword);
    }

    private function evaluateRestrictedFinancing(array $payload): bool
    {
        $value = $payload['has_restricted_financing'] ?? null;

        if (! is_bool($value)) {
            return false;
        }

        return $value === false;
    }

    private function failureReason(string $ruleType): string
    {
        return match ($ruleType) {
            'income_threshold' => 'Monthly income does not meet the minimum threshold.',
            'age_minimum' => 'Age does not meet the minimum requirement.',
            'business_category_halal' => 'Business category is not recognized as halal.',
            'no_active_restricted_financing' => 'Active restricted financing detected.',
            default => 'Rule validation failed.',
        };
    }
}
