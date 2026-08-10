package auth

import (
	"context"
	"errors"
	"safara-backend/internal/domain/user"
	"safara-backend/internal/repository/user"
	"time"

	"github.com/golang-jwt/jwt/v5"
	"github.com/google/uuid"
	"golang.org/x/crypto/bcrypt"
)

type Service interface {
	Register(ctx context.Context, name, email, password string) (*user.User, error)
	Login(ctx context.Context, email, password string) (string, *user.User, error)
}

type service struct {
	repo user.Repository
	secretKey string
}

func NewService(repo user.Repository, secretKey string) Service {
	return &service{repo: repo, secretKey: secretKey}
}

func (s *service) Register(ctx context.Context, name, email, password string) (*user.User, error) {
	hashedPassword, err := bcrypt.GenerateFromPassword([]byte(password), bcrypt.DefaultCost)
	if err != nil {
		return nil, err
	}

	entity := &user.User{
		ID:       uuid.New(),
		Name:     name,
		Email:    email,
		Password: string(hashedPassword),
		Role:     "traveler",
	}

	if err := s.repo.Create(ctx, entity); err != nil {
		return nil, err
	}
	return entity, nil
}

func (s *service) Login(ctx context.Context, email, password string) (string, *user.User, error) {
	userEntity, err := s.repo.GetByEmail(ctx, email)
	if err != nil {
		return "", nil, errors.New("invalid credentials")
	}

	if err := bcrypt.CompareHashAndPassword([]byte(userEntity.Password), []byte(password)); err != nil {
		return "", nil, errors.New("invalid credentials")
	}

	claims := jwt.MapClaims{
		"user_id": userEntity.ID.String(),
		"role":    userEntity.Role,
		"exp":     time.Now().Add(time.Hour * 72).Unix(),
	}

	token := jwt.NewWithClaims(jwt.SigningMethodHS256, claims)
	tokenString, err := token.SignedString([]byte(s.secretKey))
	if err != nil {
		return "", nil, err
	}

	return tokenString, userEntity, nil
}
