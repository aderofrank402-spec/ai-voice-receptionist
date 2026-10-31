# Helper script to run the Velocity Crawler via Docker manually
# Usage: ./test_docker_container.ps1 "https://paubarbershop.com"

param(
    [string]$url = "https://paubarbershop.com/"
)

$crawlerPath = Resolve-Path ".\crawler"

Write-Host "🐳 Running Velocity Crawler in Docker..."
Write-Host "Target: $url"
Write-Host "Mounting: $crawlerPath -> /usr/src/app"

docker run --rm --network host `
    -v "$($crawlerPath):/usr/src/app" `
    -v /usr/src/app/node_modules `
    velocity-crawler `
    npx tsx src/main.ts --url=$url
