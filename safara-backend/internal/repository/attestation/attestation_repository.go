package attestation

import (
	"context"
	"safara-backend/internal/domain/attestation"
	"time"

	"github.com/google/uuid"
	"gorm.io/gorm"
)

type repository struct {
	db *gorm.DB
}

func NewRepository(db *gorm.DB) attestation.Repository {
	return &repository{db: db}
}

func (r *repository) Create(ctx context.Context, entity *attestation.Attestation) error {
	entity.ID = uuid.New()
	entity.AttestedAt = time.Now()
	return r.db.WithContext(ctx).Create(entity).Error
}

func (r *repository) GetByVerifiedEventID(ctx context.Context, verifiedEventID uuid.UUID) (*attestation.Attestation, error) {
	var entity attestation.Attestation
	err := r.db.WithContext(ctx).Where("verified_event_id = ?", verifiedEventID).First(&entity).Error
	if err != nil {
		return nil, err
	}
	return &entity, nil
}
