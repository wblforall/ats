#!/usr/bin/env python3
"""
Generate logo WBL berstroke putih untuk cover User Guide.
Mempertahankan WARNA ASLI logo, hanya menambahkan halo/stroke putih di sekelilingnya.

  Input : ../public/assets/img/logo-wbl.png        (logo asli PNG transparan)
  Output: ../public/assets/img/logo-wbl-stroke.png (logo + stroke putih)

Ubah GROW untuk menebalkan/menipiskan stroke, lalu jalankan:
  python3 make_logo_stroke.py
Setelah itu rebuild PDF: node build_ug.js
"""
import os
from PIL import Image, ImageFilter

HERE = os.path.dirname(os.path.abspath(__file__))
IMG  = os.path.join(HERE, '..', 'public', 'assets', 'img')
SRC  = os.path.join(IMG, 'logo-wbl.png')
OUT  = os.path.join(IMG, 'logo-wbl-stroke.png')

GROW = 9            # radius stroke (px). Riwayat: 5=tipis, 9=sekarang.
KERNEL = GROW*2 + 1 # MaxFilter butuh ganjil
PAD = GROW + 6      # padding kanvas agar halo tak terpotong

src = Image.open(SRC).convert('RGBA')
w, h = src.size
canvas = Image.new('RGBA', (w+PAD*2, h+PAD*2), (0,0,0,0))
canvas.paste(src, (PAD, PAD), src)

# alpha -> dilasi jadi siluet putih (halo)
grown = canvas.split()[3].filter(ImageFilter.MaxFilter(KERNEL))
grown = grown.filter(ImageFilter.GaussianBlur(1.2))
grown = grown.point(lambda p: 255 if p > 40 else 0)   # tegaskan solid

white = Image.new('RGBA', canvas.size, (255,255,255,0))
white.putalpha(grown)

out = Image.alpha_composite(white, canvas)  # halo di bawah, logo warna asli di atas
out.save(OUT)
print(f'✓ {OUT}  size={out.size}  GROW={GROW}px')
