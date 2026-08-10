package system

import (
	"time"

	"github.com/google/uuid"
)

type AuditLog struct {
	ID         uuid.UUID `gorm:"type:uuid;primary_key" json:"id"`
	UserID     uuid.UUID `gorm:"type:uuid" json:"user_id"`
	Module     string    `gorm:"type:varchar(50);not null" json:"module"`
	Action     string    `gorm:"type:varchar(50);not null" json:"action"`
	Entity     string    `gorm:"type:varchar(50);not null" json:"entity"`
	EntityID   string    `gorm:"type:varchar(100);not null" json:"entity_id"`
	BeforeData string    `gorm:"type:jsonb" json:"before_data"`
	AfterData  string    `gorm:"type:jsonb" json:"after_data"`
	CreatedAt  time.Time `json:"created_at"`
}

func (AuditLog) TableName() string {
	return "audit_logs"
}
