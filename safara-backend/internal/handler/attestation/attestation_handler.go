package attestation

import (
	"net/http"
	appAttestation "safara-backend/internal/application/attestation"
	"safara-backend/pkg/response"

	"github.com/gin-gonic/gin"
	"github.com/google/uuid"
)

type Handler struct {
	service appAttestation.Service
}

func NewHandler(service appAttestation.Service) *Handler {
	return &Handler{service: service}
}

type AttestEventRequest struct {
	VerifiedEventID string `json:"verified_event_id" binding:"required"`
}

func (h *Handler) AttestEvent(c *gin.Context) {
	var req AttestEventRequest
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid request payload", err.Error())
		return
	}

	verifiedEventID, err := uuid.Parse(req.VerifiedEventID)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid verified_event_id format", nil)
		return
	}

	result, err := h.service.AttestEvent(c.Request.Context(), verifiedEventID)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, "Failed to create attestation", err.Error())
		return
	}

	response.Success(c, http.StatusCreated, "Attestation created successfully", result)
}
