package auth

import (
	"net/http"
	"safara-backend/internal/application/auth"

	"github.com/gin-gonic/gin"
)

type Handler struct {
	service auth.Service
}

func NewHandler(service auth.Service) *Handler {
	return &Handler{service: service}
}

func (h *Handler) Register(c *gin.Context) {
	var req struct {
		Name     string `json:"name" binding:"required"`
		Email    string `json:"email" binding:"required,email"`
		Password string `json:"password" binding:"required,min=6"`
	}

	if err := c.ShouldBindJSON(&req); err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"success": false, "message": err.Error()})
		return
	}

	userEntity, err := h.service.Register(c.Request.Context(), req.Name, req.Email, req.Password)
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"success": false, "message": "failed to register"})
		return
	}

	c.JSON(http.StatusCreated, gin.H{"success": true, "message": "registered successfully", "data": userEntity})
}

func (h *Handler) Login(c *gin.Context) {
	var req struct {
		Email    string `json:"email" binding:"required,email"`
		Password string `json:"password" binding:"required"`
	}

	if err := c.ShouldBindJSON(&req); err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"success": false, "message": err.Error()})
		return
	}

	token, userEntity, err := h.service.Login(c.Request.Context(), req.Email, req.Password)
	if err != nil {
		c.JSON(http.StatusUnauthorized, gin.H{"success": false, "message": err.Error()})
		return
	}

	c.JSON(http.StatusOK, gin.H{
		"success": true,
		"message": "login successful",
		"data": gin.H{
			"token": token,
			"user":  userEntity,
		},
	})
}
