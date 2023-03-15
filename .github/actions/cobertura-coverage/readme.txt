# How I test the action locally
cd .
export GITHUB_OUTPUT=.github/actions/cobertura-coverage/github_output.log
docker build -t cobertura-coverage .
docker run --rm --workdir /app -e "GITHUB_OUTPUT" -v "${PWD%/*/*/*}":"/app" cobertura-coverage "output/tests/coverage.cobertura.xml" "80" "true"
