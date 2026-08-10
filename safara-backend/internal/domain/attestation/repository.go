package attestation

import (
	"context"
	"github.com/google/uuid"
)

type Repository interface {
	Create(ctx context.Context, entity *Attestation) error
	GetByVerifiedEventID(ctx context.Context, verifiedEventID uuid.UUID) (*Attestation, error)
}
