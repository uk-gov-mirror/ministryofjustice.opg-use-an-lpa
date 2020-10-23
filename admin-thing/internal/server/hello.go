package server

import (
	"html/template"
	"log"
	"net/http"
)

const HelloRoute = "/"

type myThings struct {
	Name string
}

func HelloHandler(tmpl *template.Template) http.HandlerFunc {
	return func(w http.ResponseWriter, r *http.Request) {
		if r.Method == http.MethodPost {
			http.SetCookie(w, &http.Cookie{Name: "login", Value: r.FormValue("username")})
			http.Redirect(w, r, SecretRoute, http.StatusFound)
			return
		}

		name := greeterService("Josh")
		vars := myThings{Name: name}

		if err := tmpl.ExecuteTemplate(w, "page", vars); err != nil {
			log.Println(err)
		}
	}
}

func greeterService(name string) string {
	return "Hello " + name
}
