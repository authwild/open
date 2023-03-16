package main

import (
	"fmt"
	"io/ioutil"
	"net/http"
	"net/url"
	"strings"
)

func main() {
	http.HandleFunc("/", func(w http.ResponseWriter, r *http.Request) {
		// Check if the form has been submitted
		if r.Method == "POST" {
			// Get the URLs from the form input
			urls := r.FormValue("urls")
			urls_array := strings.Split(urls, "\n")

			// Initialize the result array
			results := make(map[string]string)

			// Loop through each URL and check for open redirects
			for _, u := range urls_array {
				u = strings.TrimSpace(u)
				if u != "" {
					// Check for open redirect vulnerability
					redirect_url, vulnerability := checkOpenRedirect(u)

					// Add the result to the result array
					if vulnerability {
						results[u] = redirect_url
					}
				}
			}

			// Display the results
			if len(results) > 0 {
				fmt.Fprintln(w, "<h2>Vulnerable URLs:</h2>")
				for u, r := range results {
					fmt.Fprintf(w, "<p><strong>%s</strong> - Redirects to: %s</p>", u, r)
				}
			} else {
				fmt.Fprintln(w, "<h2>No vulnerabilities found.</h2>")
			}
		} else {
			// Display the form for entering URLs
			fmt.Fprintln(w, `<form method="POST">
				<label for="urls">Enter URLs to scan (one per line):</label><br>
				<textarea name="urls" rows="10" cols="50"></textarea><br>
				<input type="submit" value="Scan">
			</form>`)
		}
	})

	// Start the web server
	http.ListenAndServe(":8080", nil)
}

// Check if the given URL is vulnerable to open redirect
// Returns the redirect URL (if any) and a boolean indicating if the URL is vulnerable
func checkOpenRedirect(u string) (string, bool) {
	// Send a GET request to the URL
	resp, err := http.Get(u)
	if err != nil {
		return "", false
	}
	defer resp.Body.Close()

	// Check if the response redirects to a different domain
	redirect_url, err := resp.Location()
	if err != nil {
		return "", false
	}
	if redirect_url != nil {
		original_url, _ := url.Parse(u)
		if redirect_url.Host != "" && redirect_url.Host != original_url.Host {
			return redirect_url.String(), true
		}
	}

	return "", false
}

