FROM golang:1
RUN go get github.com/miku/metha/cmd/...
RUN mkdir /cache
ENV METHA_DIR /cache
CMD metha-sync -u ${zbMATHUser}:${zbMATHPassword} ${zbMATHUrl}
