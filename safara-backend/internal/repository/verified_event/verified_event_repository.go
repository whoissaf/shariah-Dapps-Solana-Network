package verified_event

import (
	"context"
	"safara-backend/internal/domain/rule_engine"
	"safara-backend/internal/domain/verified_event"
	"time"

	"github.com/google/uuid"
	"gorm.io/gorm"
)

type repository struct {
	db *gorm.DB
}

func NewRepository(db *gorm.DB) verified_event.Repository {
	return &repository{db: db}
}

func (r *repository) CreateVerificationRequest(ctx context.Context, req *verified_event.VerificationRequest) error {
	req.ID = uuid.New()
	req.CreatedAt = time.Now()
	req.UpdatedAt = time.Now()
	return r.db.WithContext(ctx).Create(req).Error
}

func (r *repository) CreateVerificationLog(ctx context.Context, log *verified_event.VerificationLog) error {
	log.ID = uuid.New()
	log.CreatedAt = time.Now()
	return r.db.WithContext(ctx).Create(log).Error
}

func (r *repository) CreateVerifiedEvent(ctx context.Context, event *verified_event.VerifiedEvent) error {
	event.ID = uuid.New()
	event.CreatedAt = time.Now()
	event.UpdatedAt = time.Now()
	return r.db.WithContext(ctx).Create(event).Error
}

func (r *repository) GetByID(ctx context.Context, id uuid.UUID) (*verified_event.VerifiedEvent, error) {
	var entity verified_event.VerifiedEvent
	err := r.db.WithContext(ctx).Where("id = ?", id).First(&entity).Error
	if err != nil {
		return nil, err
	}
	return &entity, nil
}

func (r *repository) CreateRuleSnapshot(ctx context.Context, snapshot *rule_engine.RuleSnapshot) error {
	snapshot.ID = uuid.New()
	snapshot.GeneratedAt = time.Now()
	return r.db.WithContext(ctx).Create(snapshot).Error
}

func (r *repository) CreateRuleReasons(ctx context.Context, reasons []rule_engine.RuleReason) error {
	for i := range reasons {
		reasons[i].ID = uuid.New()
	}
	return r.db.WithContext(ctx).Create(&reasons).Error
}
