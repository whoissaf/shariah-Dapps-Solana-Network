package ai

import (
	"context"
	"safara-backend/internal/domain/ai"
	"time"

	"github.com/google/uuid"
	"gorm.io/gorm"
)

type repository struct {
	db *gorm.DB
}

func NewRepository(db *gorm.DB) ai.Repository {
	return &repository{db: db}
}

func (r *repository) Create(ctx context.Context, entity *ai.AIExplanation) error {
	entity.ID = uuid.New()
	entity.CreatedAt = time.Now()
	return r.db.WithContext(ctx).Create(entity).Error
}

func (r *repository) GetBySnapshotID(ctx context.Context, snapshotID uuid.UUID) (*ai.AIExplanation, error) {
	var entity ai.AIExplanation
	err := r.db.WithContext(ctx).Where("snapshot_id = ?", snapshotID).First(&entity).Error
	if err != nil {
		return nil, err
	}
	return &entity, nil
}
