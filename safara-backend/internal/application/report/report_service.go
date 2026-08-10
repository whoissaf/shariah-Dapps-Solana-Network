package report

import (
	"context"
	"safara-backend/internal/domain/report"

	"github.com/google/uuid"
)

type Service interface {
	SubmitReport(ctx context.Context, req SubmitReportRequest) (*report.Report, error)
}

type SubmitReportRequest struct {
	UserID      uuid.UUID
	LocationID  uuid.UUID
	Category    string
	Title       string
	Description string
}

type service struct {
	repo report.Repository
}

func NewService(repo report.Repository) Service {
	return &service{repo: repo}
}

func (s *service) SubmitReport(ctx context.Context, req SubmitReportRequest) (*report.Report, error) {
	entity := &report.Report{
		UserID:      req.UserID,
		LocationID:  req.LocationID,
		Category:    req.Category,
		Title:       req.Title,
		Description: req.Description,
		Status:      "submitted",
	}

	if err := s.repo.Create(ctx, entity); err != nil {
		return nil, err
	}

	return entity, nil
}
