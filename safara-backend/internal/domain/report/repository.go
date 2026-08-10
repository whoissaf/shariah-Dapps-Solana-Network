package report

import (
	"context"
	"github.com/google/uuid"
)

type Repository interface {
	Create(ctx context.Context, report *Report) error
	GetByID(ctx context.Context, id uuid.UUID) (*Report, error)
	UpdateStatus(ctx context.Context, id uuid.UUID, status string) error
}
