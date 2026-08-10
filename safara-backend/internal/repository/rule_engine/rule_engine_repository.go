package rule_engine

import (
	"context"
	"safara-backend/internal/domain/rule_engine"
	"time"

	"github.com/google/uuid"
	"gorm.io/gorm"
)

type repository struct {
	db *gorm.DB
}

func NewRepository(db *gorm.DB) rule_engine.Repository {
	return &repository{db: db}
}

func (r *repository) CreateSnapshot(ctx context.Context, snapshot *rule_engine.RuleSnapshot) error {
	snapshot.ID = uuid.New()
	snapshot.GeneratedAt = time.Now()
	return r.db.WithContext(ctx).Create(snapshot).Error
}

func (r *repository) GetSnapshotByID(ctx context.Context, id uuid.UUID) (*rule_engine.RuleSnapshot, error) {
	var entity rule_engine.RuleSnapshot
	err := r.db.WithContext(ctx).Where("id = ?", id).First(&entity).Error
	if err != nil {
		return nil, err
	}
	return &entity, nil
}

func (r *repository) CreateReasons(ctx context.Context, reasons []rule_engine.RuleReason) error {
	for i := range reasons {
		reasons[i].ID = uuid.New()
	}
	return r.db.WithContext(ctx).Create(&reasons).Error
}

func (r *repository) GetReasonsBySnapshotID(ctx context.Context, snapshotID uuid.UUID) ([]rule_engine.RuleReason, error) {
	var reasons []rule_engine.RuleReason
	err := r.db.WithContext(ctx).Where("snapshot_id = ?", snapshotID).Find(&reasons).Error
	if err != nil {
		return nil, err
	}
	return reasons, nil
}
