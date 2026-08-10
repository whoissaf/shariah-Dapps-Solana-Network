package ai

import (
	"context"
	"github.com/google/uuid"
)

type Repository interface {
	Create(ctx context.Context, entity *AIExplanation) error
	GetBySnapshotID(ctx context.Context, snapshotID uuid.UUID) (*AIExplanation, error)
}
