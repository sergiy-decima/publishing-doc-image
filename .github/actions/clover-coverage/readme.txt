# How I test the action locally
cd .
export GITHUB_OUTPUT=.github/actions/clover-coverage/github_output.log
docker build -t clover-coverage .
docker run --rm --workdir /app -e "GITHUB_OUTPUT" -v "${PWD%/*/*/*}":"/app" clover-coverage "output/tests/coverage.clover.xml" "60..80" "true"
