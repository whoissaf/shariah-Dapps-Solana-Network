<?php

namespace App\Services;

use App\Models\Claim;
use App\Models\Document;
use App\Models\Identity;
use App\Models\Rule;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RuleValidationService
{
    public function __construct(private RuleEvaluator $ruleEvaluator)
    {
    }

    public function validateClaim(User $user, int $claimId): array
    {
        return DB::transaction(function () use ($user, $claimId) {
            $claim = Claim::where('user_id', $user->id)->find($claimId);

            if (! $claim) {
                throw ValidationException::withMessages([
                    'claim_id' => ['Claim not found.'],
                ]);
            }

            if (! in_array($claim->status, ['submitted', 'eligible', 'ineligible'])) {
                throw ValidationException::withMessages([
                    'claim' => ['Claim cannot be validated in current status.'],
                ]);
            }

            $documentCount = Document::where('claim_id', $claim->id)->count();

            if ($documentCount === 0) {
                throw ValidationException::withMessages([
                    'documents' => ['Upload at least one supporting document before rule validation.'],
                ]);
            }

            $ruleCount = Rule::where('rule_type', $claim->claim_type)
                ->where('is_active', true)
                ->count();

            if ($ruleCount === 0) {
                throw ValidationException::withMessages([
                    'rules' => ['No active rules found for this claim type.'],
                ]);
            }

            $evaluation = $this->ruleEvaluator->evaluate($claim);

            $eligible = $evaluation['eligible'];
            $results = $evaluation['results'];

            $claim->forceFill([
                'status' => $eligible ? 'eligible' : 'ineligible',
            ])->save();

            return [
                'message' => $eligible ? 'Claim is eligible.' : 'Claim is ineligible.',
                'claim' => [
                    'id' => $claim->id,
                    'claim_type' => $claim->claim_type,
                    'status' => $claim->status,
                    'submitted_at' => $claim->submitted_at?->toIso8601String(),
                ],
                'eligible' => $eligible,
                'results' => $results,
            ];
        });
    }
}
