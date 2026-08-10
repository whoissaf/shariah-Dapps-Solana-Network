package rule_engine

import (
	"context"
	"github.com/google/uuid"
)

type Repository interface {
	CreateSnapshot(ctx context.Context, snapshot *RuleSnapshot) error
	GetSnapshotByID(ctx context.Context, id uuid.UUID) (*RuleSnapshot, error)
	CreateReasons(ctx context.Context, reasons []RuleReason) error
	GetReasonsBySnapshotID(ctx context.Context, snapshotID uuid.UUID) ([]RuleReason, error)
}
