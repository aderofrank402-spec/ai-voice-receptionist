# Use Node.js 20 (LTS) - Bullseye (Standard Linux)
FROM node:20-bullseye

# 1. Install System Dependencies for Playwright
# This installs all the libs (libnss, libatk, etc.) required to run Chromium headlessly
RUN npx playwright install-deps

# 2. Create Directory
WORKDIR /usr/src/app

# 3. Copy Package Definitions
# We copy these first to leverage Docker layer caching for npm install
COPY package*.json ./

# 4. Install Project Dependencies
RUN npm ci

# 5. Install Playwright Browsers (Binary)
RUN npx playwright install chromium

# 6. Copy Source Code
COPY . .

# 7. Build TypeScript (Optimizes startup speed)
RUN npm run build

# 8. Run Compiled JS (Fastest startup)
CMD ["node", "dist/main.js"]
