package report

import (
	"context"
	"safara-backend/internal/domain/report"
	"time"

	"github.com/google/uuid"
	"gorm.io/gorm"
)

type reportRepository struct {
	db *gorm.DB
}

func NewReportRepository(db *gorm.DB) report.Repository {
	return &reportRepository{db: db}
}

func (r *reportRepository) Create(ctx context.Context, entity *report.Report) error {
	entity.ID = uuid.New()
	entity.SubmittedAt = time.Now()
	entity.CreatedAt = time.Now()
	entity.UpdatedAt = time.Now()
	return r.db.WithContext(ctx).Create(entity).Error
}

func (r *reportRepository) GetByID(ctx context.Context, id uuid.UUID) (*report.Report, error) {
	var entity report.Report
	err := r.db.WithContext(ctx).Where("id = ?", id).First(&entity).Error
	if err != nil {
		return nil, err
	}
	return &entity, nil
}

func (r *reportRepository) UpdateStatus(ctx context.Context, id uuid.UUID, status string) error {
	return r.db.WithContext(ctx).Model(&report.Report{}).Where("id = ?", id).Update("status", status).Error
}
