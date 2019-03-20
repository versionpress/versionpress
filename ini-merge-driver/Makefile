NAME := merge-driver
BUILDS=\
  darwin-amd64  \
  linux-386     \
  linux-amd64   \
  windows-386   \
  windows-amd64 \

build: $(BUILDS)

$(BUILDS): OS = $(word 1,$(subst -, ,$*))
$(BUILDS): ARCH = $(word 2,$(subst -, ,$*))

$(BUILDS): %:
		@GOOS=$(OS) GOARCH=$(ARCH) go build -ldflags="-s -w" -o dist/$(NAME)-$(OS)-$(ARCH) merge_driver.go
		@upx --brute dist/$(NAME)-$(OS)-$(ARCH)
