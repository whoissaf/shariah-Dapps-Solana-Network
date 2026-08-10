package verified_event

import (
	"context"
	"safara-backend/internal/domain/rule_engine"
	"github.com/google/uuid"
)

type Repository interface {
	CreateVerificationRequest(ctx context.Context, req *VerificationRequest) error
	CreateVerificationLog(ctx context.Context, log *VerificationLog) error
	CreateVerifiedEvent(ctx context.Context, event *VerifiedEvent) error
	GetByID(ctx context.Context, id uuid.UUID) (*VerifiedEvent, error)
	CreateRuleSnapshot(ctx context.Context, snapshot *rule_engine.RuleSnapshot) error
	CreateRuleReasons(ctx context.Context, reasons []rule_engine.RuleReason) error
}
