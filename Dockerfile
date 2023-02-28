FROM node:19-alpine
LABEL org.opencontainers.image.source="https://github.com/sergiy-decima/publishing-doc-image"
COPY . /app
WORKDIR /app
CMD node app.js
