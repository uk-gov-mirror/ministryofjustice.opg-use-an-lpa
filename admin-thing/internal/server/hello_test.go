package server

import (
	"io"
	"net/http"
	"net/http/httptest"
	"testing"

	"gotest.tools/assert"
)

type mockTemplate struct{}

func (m *mockTemplate) ExecuteTemplate(w io.Writer, name string, data interface{}) error {
	return nil
}

func TestHelloHandler(t *testing.T) {
	handler := HelloHandler(&mockTemplate{})

	w := httptest.NewRecorder()
	r := httptest.NewRequest(http.MethodGet, "/", nil)

	handler(w, r)

	resp := w.Result()
	assert.Equal(t, resp.StatusCode, http.StatusOK)
}
