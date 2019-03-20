NAME := merge-driver
BUILDS=\
  darwin-amd64  \
  linux-386     \
  linux-amd64   \
  windows-386   \
  windows-amd64 \

UPX_COMPRESSION ?= --brute

build: $(BUILDS)
	@rm 'dist/*.*' 2> /dev/null || true

$(BUILDS): OS = $(word 1,$(subst -, ,$*))
$(BUILDS): ARCH = $(word 2,$(subst -, ,$*))

$(BUILDS): %:
		@GOOS=$(OS) GOARCH=$(ARCH) go build -ldflags="-s -w" -o dist/$(NAME)-$(OS)-$(ARCH) merge_driver.go
		@upx $(UPX_COMPRESSION) dist/$(NAME)-$(OS)-$(ARCH)

build-dev:
	@make build UPX_COMPRESSION=-1
