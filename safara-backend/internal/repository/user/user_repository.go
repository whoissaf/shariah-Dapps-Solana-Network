package user

import (
	"context"
	"safara-backend/internal/domain/user"
	"gorm.io/gorm"
)

type Repository interface {
	Create(ctx context.Context, entity *user.User) error
	GetByEmail(ctx context.Context, email string) (*user.User, error)
	GetByID(ctx context.Context, id string) (*user.User, error)
}

type repository struct {
	db *gorm.DB
}

func NewRepository(db *gorm.DB) Repository {
	return &repository{db: db}
}

func (r *repository) Create(ctx context.Context, entity *user.User) error {
	return r.db.WithContext(ctx).Create(entity).Error
}

func (r *repository) GetByEmail(ctx context.Context, email string) (*user.User, error) {
	var entity user.User
	err := r.db.WithContext(ctx).Where("email = ?", email).First(&entity).Error
	return &entity, err
}

func (r *repository) GetByID(ctx context.Context, id string) (*user.User, error) {
	var entity user.User
	err := r.db.WithContext(ctx).Where("id = ?", id).First(&entity).Error
	return &entity, err
}
