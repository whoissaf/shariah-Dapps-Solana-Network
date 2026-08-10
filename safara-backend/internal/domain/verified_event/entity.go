package verified_event

import (
	"time"

	"github.com/google/uuid"
	"gorm.io/gorm"
)

type VerificationRequest struct {
	ID         uuid.UUID `gorm:"type:uuid;primary_key" json:"id"`
	ReportID   uuid.UUID `gorm:"type:uuid;uniqueIndex;not null" json:"report_id"`
	Status     string    `gorm:"type:varchar(20);default:'pending'" json:"status"`
	AssignedTo uuid.UUID `gorm:"type:uuid" json:"assigned_to"`
	CreatedAt  time.Time `json:"created_at"`
	UpdatedAt  time.Time `json:"updated_at"`
}

func (VerificationRequest) TableName() string {
	return "verification_requests"
}

type VerificationLog struct {
	ID             uuid.UUID `gorm:"type:uuid;primary_key" json:"id"`
	VerificationID uuid.UUID `gorm:"type:uuid;not null" json:"verification_id"`
	Action         string    `gorm:"type:varchar(50);not null" json:"action"`
	Note           string    `gorm:"type:text" json:"note"`
	CreatedBy      uuid.UUID `gorm:"type:uuid;not null" json:"created_by"`
	CreatedAt      time.Time `json:"created_at"`
}

func (VerificationLog) TableName() string {
	return "verification_logs"
}

type VerifiedEvent struct {
	ID             uuid.UUID      `gorm:"type:uuid;primary_key" json:"id"`
	ReportID       uuid.UUID      `gorm:"type:uuid;uniqueIndex;not null" json:"report_id"`
	VerificationID uuid.UUID      `gorm:"type:uuid;uniqueIndex;not null" json:"verification_id"`
	Version        int            `gorm:"type:int;default:1" json:"version"`
	EventTime      time.Time      `json:"event_time"`
	Status         string         `gorm:"type:varchar(20);default:'verified'" json:"status"`
	CreatedAt      time.Time      `json:"created_at"`
	UpdatedAt      time.Time      `json:"updated_at"`
	DeletedAt      gorm.DeletedAt `gorm:"index" json:"-"`
}

func (VerifiedEvent) TableName() string {
	return "verified_events"
}
