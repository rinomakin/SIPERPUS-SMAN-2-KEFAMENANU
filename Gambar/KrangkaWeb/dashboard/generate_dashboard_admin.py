from PIL import Image, ImageDraw, ImageFont

# ── Canvas ──────────────────────────────────────────────
W, H = 1220, 1380
img  = Image.new('RGB', (W, H), '#d4d4d4')
draw = ImageDraw.Draw(img)

# ── Palette ──────────────────────────────────────────────
C = {
    'browser_top' : '#2b2b2b',
    'tab_active'  : '#f0f0f0',
    'tab_inactive': '#3a3a3a',
    'tab_text_a'  : '#222222',
    'tab_text_i'  : '#aaaaaa',
    'addr_bar'    : '#ffffff',
    'addr_text'   : '#444444',
    'sidebar'     : '#2c2c2c',
    'sidebar_act' : '#3e3e3e',
    'sidebar_txt' : '#cccccc',
    'sidebar_sub' : '#888888',
    'sidebar_hdr' : '#666666',
    'content_bg'  : '#f5f5f5',
    'white'       : '#ffffff',
    'border'      : '#dddddd',
    'border_d'    : '#bbbbbb',
    'text_dark'   : '#222222',
    'text_med'    : '#555555',
    'text_light'  : '#888888',
    'icon_box'    : '#cccccc',
    'icon_dark'   : '#555555',
    'btn_dark'    : '#333333',
    'btn_light'   : '#e0e0e0',
    'tbl_hdr'     : '#eeeeee',
    'tbl_alt'     : '#fafafa',
    'badge_gray'  : '#dddddd',
    'badge_dark'  : '#444444',
    'taskbar'     : '#1a1a1a',
    'hero_dark'   : '#444444',
    'hero_mid'    : '#666666',
    'hero_light'  : '#888888',
}

# ── Fonts ────────────────────────────────────────────────
def fnt(size, bold=False):
    paths_b = ['C:/Windows/Fonts/segoeuib.ttf','C:/Windows/Fonts/arialbd.ttf']
    paths_r = ['C:/Windows/Fonts/segoeui.ttf', 'C:/Windows/Fonts/arial.ttf']
    for p in (paths_b if bold else paths_r):
        try: return ImageFont.truetype(p, size)
        except: pass
    return ImageFont.load_default()

# ── Draw helpers ─────────────────────────────────────────
def rect(x1,y1,x2,y2,fill,outline=None,ow=1):
    draw.rectangle([x1,y1,x2,y2], fill=fill,
                   outline=outline or fill, width=ow)

def txt(x,y,s,color,size,bold=False):
    draw.text((x,y), str(s), fill=color, font=fnt(size,bold))

def hline(x1,x2,y,color='#dddddd',w=1):
    draw.line([x1,y,x2,y], fill=color, width=w)

def icon_box(x,y,w,h,label='',bg='#cccccc',fg='#555555',sz=8):
    rect(x,y,x+w,y+h,bg,'#bbbbbb')
    if label: txt(x+3,y+2,label,fg,sz)

def badge(x,y,label,bg='#dddddd',fg='#444444',sz=9):
    w = len(label)*5+12
    rect(x,y,x+w,y+14,bg)
    txt(x+5,y+2,label,fg,sz)
    return x+w

def avatar_circle(cx,cy,r,bg,letter,fg='#ffffff',sz=11):
    draw.ellipse([cx-r,cy-r,cx+r,cy+r],fill=bg)
    txt(cx-4,cy-7,letter,fg,sz,bold=True)

def progress_bar(x,y,w,h,pct,bg='#dddddd',fg='#888888'):
    rect(x,y,x+w,y+h,bg)
    rect(x,y,x+int(w*pct/100),y+h,fg)

# ═══════════════════════════════════════════════════════
# BROWSER CHROME
# ═══════════════════════════════════════════════════════
rect(0,0,W,30,C['browser_top'])
# Inactive tab
rect(5,5,230,30,C['tab_inactive'])
txt(14,10,'(32) Raim Laode - Bersenj...',C['tab_text_i'],9)
txt(210,9,'x',C['tab_text_i'],10)
# Active tab
rect(232,4,470,30,C['tab_active'])
txt(246,10,'SIPERPUS - Admin',C['tab_text_a'],11)
txt(452,9,'x',C['tab_text_a'],10)
txt(476,9,'+',C['tab_text_i'],11)
# Window controls
for ox,lbl,bg in [(W-110,'—','#2b2b2b'),(W-74,'□','#2b2b2b'),(W-37,'✕','#c0392b')]:
    rect(ox,0,ox+36,30,bg)
    txt(ox+9,9,lbl,'#ffffff',11)

# Address bar
rect(0,30,W,62,'#f0f0f0','#cccccc')
for xi,lbl in [(8,'<'),(28,'>'),(48,'C')]:
    txt(xi,40,lbl,C['addr_text'],12)
rect(72,37,880,56,C['addr_bar'],C['border_d'])
txt(80,42,'127.0.0.1:8000/admin/dashboard',C['addr_text'],11)
for i in range(7):
    cx,cy=896+i*40,48
    draw.ellipse([cx-13,cy-13,cx+13,cy+13],outline='#cccccc',width=1)

CY  = 62    # browser end
SBW = 200   # sidebar width
MX  = SBW   # main content start x
PAD = 14    # content padding

# ═══════════════════════════════════════════════════════
# SIDEBAR
# ═══════════════════════════════════════════════════════
rect(0,CY,SBW,H,C['sidebar'])
avatar_circle(26,CY+24,13,'#555555','SP','#ffffff',10)
txt(46,CY+14,'SIPERPUS','#ffffff',13,bold=True)
txt(46,CY+30,'Admin Panel',C['sidebar_sub'],9)
hline(0,SBW,CY+50,'#444444')

def sb_sec(y,lbl):
    txt(10,y,lbl,C['sidebar_hdr'],8,bold=True)
    return y+16

def sb_item(y,lbl,active=False,indent=False):
    ix=22 if indent else 10
    if active:
        rect(0,y-1,SBW,y+18,C['sidebar_act'])
        rect(0,y-1,3,y+18,'#888888')
    txt(ix,y+2,lbl,'#ffffff' if active else C['sidebar_txt'],11)
    return y+21

sy=CY+58
sy=sb_item(sy,'Dashboard',active=True)
sy+=4; sy=sb_sec(sy,'MASTER DATA')
sy=sb_item(sy,'Data Master')
sy+=4; sy=sb_sec(sy,'MANAJEMEN DATA')
sy=sb_item(sy,'Data User',indent=True)
sy=sb_item(sy,'Data Anggota',indent=True)
sy=sb_item(sy,'Data Buku',indent=True)
sy+=4; sy=sb_sec(sy,'TRANSAKSI')
sy=sb_item(sy,'Peminjaman',indent=True)
sy=sb_item(sy,'Pengembalian',indent=True)
sy=sb_item(sy,'Denda',indent=True)
sy=sb_item(sy,'Buku Tamu',indent=True)
sy+=4; sy=sb_sec(sy,'LAPORAN')
sy=sb_item(sy,'Laporan',indent=True)
sy+=4; sy=sb_sec(sy,'PENGATURAN')
sy=sb_item(sy,'Pengaturan Website',indent=True)

hline(0,SBW,H-36,'#444444')
txt(10,H-26,'< Tutup Sidebar',C['sidebar_hdr'],10)

# ═══════════════════════════════════════════════════════
# MAIN CONTENT
# ═══════════════════════════════════════════════════════
rect(MX,CY,W,H,C['content_bg'])

# ── Topbar ───────────────────────────────────────────────
rect(MX,CY,W,CY+46,C['white'])
hline(MX,W,CY+46)
txt(MX+14,CY+15,'x',C['text_light'],13)
txt(MX+30,CY+14,'Dashboard',C['text_dark'],15,bold=True)
# Gear icon placeholder
icon_box(W-180,CY+12,24,24,'[⚙]',C['btn_light'],C['text_med'],9)
# User avatar badge
avatar_circle(W-30,CY+23,15,'#555555','R','#ffffff',11)
rect(W-115,CY+8,W-48,CY+40,'#eeeeee','#dddddd')
txt(W-110,CY+11,'Rdm',C['text_dark'],12,bold=True)
txt(W-110,CY+27,'Administrator',C['text_light'],9)
txt(W-52,CY+20,'v',C['text_light'],9)

# ── Breadcrumb ───────────────────────────────────────────
BY=CY+46
rect(MX,BY,W,BY+24,'#f9f9f9','#eeeeee')
hline(MX,W,BY+24,'#eeeeee')
txt(MX+12,BY+6,'⌂  Dashboard',C['text_light'],10)

# Content body
y = BY+24+PAD   # current y pointer

# ═══════════════════════════════════════════════════════
# 1. WELCOME HERO BANNER
# ═══════════════════════════════════════════════════════
HERO_H = 110
HERO_X1 = MX+PAD;  HERO_X2 = W-PAD
rect(HERO_X1,y,HERO_X2,y+HERO_H,'#555555','#444444')
# Diagonal gradient effect (stripes)
for gi in range(0,700,12):
    x1g=HERO_X1+gi; x2g=x1g+8
    if x2g>HERO_X2: x2g=HERO_X2
    draw.rectangle([x1g,y,x2g,y+HERO_H],fill='#5a5a5a')
# Overlay darker band left
rect(HERO_X1,y,HERO_X1+420,y+HERO_H,'#505050')

# Text in hero
txt(HERO_X1+18,y+12,'Selamat Pagi,','#cccccc',10)
txt(HERO_X1+18,y+26,'Rdm!','#ffffff',20,bold=True)
txt(HERO_X1+18,y+54,'SIPERPUS','#bbbbbb',10)
# Date badge
rect(HERO_X1+18,y+70,HERO_X1+168,y+88,'#666666')
txt(HERO_X1+24,y+73,'[kal]  Senin, 23 Maret 2026','#dddddd',9)
# Clock badge
rect(HERO_X1+176,y+70,HERO_X1+256,y+88,'#666666')
txt(HERO_X1+182,y+73,'[clk]  01:05','#dddddd',9)
# Button: Peminjaman Baru
rect(HERO_X2-168,y+36,HERO_X2-16,y+66,'#777777','#999999')
txt(HERO_X2-158,y+44,'+ Peminjaman Baru','#ffffff',11,bold=True)
# Decorative book icon (big placeholder)
icon_box(HERO_X2-120,y+10,90,80,'[📖]','#606060','#aaaaaa',22)

y += HERO_H+12

# ═══════════════════════════════════════════════════════
# 2. STAT CARDS (4 cards)
# ═══════════════════════════════════════════════════════
CARD_H = 105
avail  = W-MX-PAD*2
cw     = (avail-12*3)//4

cards=[
    ('Total Anggota',    '6',        '[anggota]','↑ 0'),
    ('Total Judul Buku', '5',        '[buku]',   'Koleksi'),
    ('Peminjaman Aktif', '2',        '[pinjam]', '179 buku'),
    ('Total Denda',      'Rp 37.000','[denda]',  'Denda'),
]
for i,(lbl,num,ico,bdg) in enumerate(cards):
    cx=MX+PAD+i*(cw+12)
    rect(cx,y,cx+cw,y+CARD_H,C['white'],C['border'])
    # Icon box top-left
    icon_box(cx+10,y+12,38,38,ico,C['icon_box'],C['icon_dark'],9)
    # Badge top-right
    badge(cx+cw-74,y+14,bdg,C['badge_gray'],C['badge_dark'],8)
    # Number
    txt(cx+10,y+56,num,C['text_dark'],18,bold=True)
    # Label
    txt(cx+10,y+82,lbl,C['text_light'],10)
    # Progress bar bottom
    progress_bar(cx+10,y+CARD_H-10,cw-20,5,30,'#eeeeee','#aaaaaa')

y += CARD_H+12

# ═══════════════════════════════════════════════════════
# 3. CHARTS SECTION (2/3 bar chart + 1/3 donut)
# ═══════════════════════════════════════════════════════
CHART_H = 240
main_w  = int((W-MX-PAD*2)*2/3)-6
side_w  = (W-MX-PAD*2)-main_w-12

# ── Bar chart card ────────────────────────────────────
bx=MX+PAD
rect(bx,y,bx+main_w,y+CHART_H,C['white'],C['border'])
txt(bx+12,y+12,'Statistik Peminjaman & Pengembalian',C['text_dark'],12,bold=True)
txt(bx+12,y+28,'6 bulan terakhir',C['text_light'],9)
# Legend
lx=bx+main_w-170
rect(lx,y+14,lx+10,y+24,'#888888')
txt(lx+13,y+14,'Pinjam',C['text_light'],9)
rect(lx+60,y+14,lx+70,y+24,'#aaaaaa')
txt(lx+73,y+14,'Kembali',C['text_light'],9)
# Chart area
ch_y=y+44; ch_h=CHART_H-60
# Grid lines + y-axis labels
for gi,val in [(0,25),(1,20),(2,15),(3,10),(4,5)]:
    gy=ch_y+gi*(ch_h-14)//4
    hline(bx+32,bx+main_w-10,gy,'#eeeeee')
    txt(bx+10,gy-6,str(val),C['text_light'],8)
hline(bx+32,bx+main_w-10,ch_y+ch_h-14,'#cccccc')

months=['Okt','Nov','Des','Jan','Feb','Mar']
vals_p=[8,12,10,18,14,20]
vals_k=[6,10,12,15,12,18]
baw=(main_w-42)//len(months); bmax=25
for mi,(mon,vp,vk) in enumerate(zip(months,vals_p,vals_k)):
    gx=bx+32+mi*baw+4
    bar_area_h=ch_h-16
    # Peminjaman bar (dark gray)
    ph=int(bar_area_h*vp/bmax)
    rect(gx,ch_y+bar_area_h-ph,gx+baw//3,ch_y+bar_area_h,'#888888')
    # Pengembalian bar (light gray)
    kh=int(bar_area_h*vk/bmax)
    rect(gx+baw//3+2,ch_y+bar_area_h-kh,gx+baw*2//3+2,ch_y+bar_area_h,'#bbbbbb')
    txt(gx+2,ch_y+ch_h-12,mon,C['text_light'],8)

# ── Donut chart card ──────────────────────────────────
dx=bx+main_w+12
rect(dx,y,dx+side_w,y+CHART_H,C['white'],C['border'])
txt(dx+12,y+12,'Kategori Populer',C['text_dark'],12,bold=True)
txt(dx+12,y+28,'Distribusi koleksi buku',C['text_light'],9)
# Donut circle
cx_d=dx+side_w//2; cy_d=y+CHART_H//2+10
r_out=65; r_in=42
draw.ellipse([cx_d-r_out,cy_d-r_out,cx_d+r_out,cy_d+r_out],fill='#cccccc')
draw.ellipse([cx_d-r_in, cy_d-r_in, cx_d+r_in, cy_d+r_in], fill=C['white'])
# Donut segments (simple quadrant split)
seg_colors=['#888888','#aaaaaa','#999999','#bbbbbb','#777777']
seg_labels=['Fiksi','Sains','Sejarah','Bahasa','Lainnya']
from PIL import ImageDraw as ID
import math
angles=[0,90,160,220,290,360]
for si in range(len(seg_labels)):
    a1=angles[si]; a2=angles[si+1]
    draw.pieslice([cx_d-r_out,cy_d-r_out,cx_d+r_out,cy_d+r_out],
                  start=a1,end=a2,fill=seg_colors[si])
draw.ellipse([cx_d-r_in,cy_d-r_in,cx_d+r_in,cy_d+r_in],fill=C['white'])
# Legend below donut
for li,lbl in enumerate(seg_labels[:4]):
    lx=dx+8+(li%2)*((side_w-16)//2)
    ly=y+CHART_H-42+(li//2)*16
    rect(lx,ly+3,lx+8,ly+11,seg_colors[li])
    txt(lx+11,ly+2,lbl,C['text_light'],8)

y += CHART_H+12

# ═══════════════════════════════════════════════════════
# 4. BOTTOM SECTION (2/3 activities + 1/3 right col)
# ═══════════════════════════════════════════════════════
BOT_H = 380
act_w = main_w          # same as chart main col
right_w = side_w        # same as chart side col

# ── Aktivitas Terbaru (2/3) ───────────────────────────
ax=MX+PAD
rect(ax,y,ax+act_w,y+BOT_H,C['white'],C['border'])
txt(ax+12,y+12,'Aktivitas Terbaru',C['text_dark'],12,bold=True)
txt(ax+12,y+28,'Peminjaman dan pengembalian terbaru',C['text_light'],9)
txt(ax+act_w-68,y+15,'Lihat Semua »',C['text_med'],9)
hline(ax,ax+act_w,y+44)

activities=[
    ('Rikardus Doni Muda Makin','Peminjaman','2 jam lalu','[→]'),
    ('Jonathan Prem Pandie',    'Pengembalian','5 jam lalu','[✓]'),
    ('Agustinho M. Eco',        'Peminjaman','1 hari lalu','[→]'),
    ('Adonia Amsaljao',         'Pengembalian','1 hari lalu','[✓]'),
    ('Sesar Naben',             'Peminjaman','2 hari lalu','[→]'),
    ('Oswaldus Kefi',           'Pengembalian','2 hari lalu','[✓]'),
    ('Rikardus Doni Muda Makin','Peminjaman','3 hari lalu','[→]'),
]
for ai,act in enumerate(activities):
    nama,tipe,waktu,ico=act
    ay=y+52+ai*44
    if ay+40>y+BOT_H: break
    # Activity item box
    rect(ax+10,ay,ax+act_w-10,ay+38,'#fafafa','#eeeeee')
    # Icon
    icon_box(ax+16,ay+8,26,22,ico,C['icon_box'],C['icon_dark'],9)
    # Name + type
    txt(ax+50,ay+8,nama,C['text_dark'],11,bold=True)
    badge(ax+50,ay+24,tipe,C['badge_gray'],C['badge_dark'],9)
    # Time
    txt(ax+act_w-74,ay+16,waktu,C['text_light'],9)

# ── Right Column: Buku Terpopuler + Aksi Cepat ───────
rx=ax+act_w+12
# Buku Terpopuler card
POP_H=190
rect(rx,y,rx+right_w,y+POP_H,C['white'],C['border'])
txt(rx+12,y+12,'Buku Terpopuler',C['text_dark'],12,bold=True)
txt(rx+12,y+28,'Paling sering dipinjam',C['text_light'],9)
hline(rx,rx+right_w,y+44)

buku_pop=[
    ('1','Laskar Pelangi',       '12 kali','#888888'),  # gold->dark
    ('2','Harry Potter',          '9 kali', '#aaaaaa'),  # silver->mid
    ('3','Bumi Manusia',          '7 kali', '#999999'),  # bronze->mid
    ('4','Negeri 5 Menara',       '5 kali', '#cccccc'),
    ('5','Ayat-ayat Cinta',       '4 kali', '#cccccc'),
]
for bi,(rank,judul,kali,rcol) in enumerate(buku_pop):
    by=y+52+bi*26
    if by+22>y+POP_H: break
    # Rank badge
    draw.ellipse([rx+12,by+2,rx+26,by+20],fill=rcol)
    txt(rx+15,by+4,rank,'#ffffff',9,bold=True)
    txt(rx+32,by+4,judul,C['text_dark'],10)
    txt(rx+right_w-60,by+4,kali,C['text_light'],9)

# Aksi Cepat card
qy=y+POP_H+10
QA_H=BOT_H-POP_H-10
rect(rx,qy,rx+right_w,qy+QA_H,C['white'],C['border'])
txt(rx+12,qy+12,'Aksi Cepat',C['text_dark'],12,bold=True)
hline(rx,rx+right_w,qy+34)

qa_items=[
    ('Pinjam Buku',  '[+]',  '#888888','#eeeeee'),
    ('Kembalikan',   '[↩]',  '#999999','#eeeeee'),
    ('Anggota Baru', '[👤]', '#aaaaaa','#f0f0f0'),
    ('Tambah Buku',  '[📗]', '#888888','#f0f0f0'),
]
qa_w=(right_w-30)//2
qa_h=(QA_H-50)//2
for qi,( lbl,ico,ibg,cbg) in enumerate(qa_items):
    row,col=divmod(qi,2)
    qix=rx+10+col*(qa_w+10)
    qiy=qy+42+row*(qa_h+8)
    rect(qix,qiy,qix+qa_w,qiy+qa_h,cbg,'#dddddd')
    # Icon box centered
    icon_box(qix+qa_w//2-14,qiy+8,28,24,ico,ibg,'#ffffff',10)
    txt(qix+qa_w//2-len(lbl)*3,qiy+38,lbl,C['text_med'],9,bold=True)

# ═══════════════════════════════════════════════════════
# WINDOWS TASKBAR
# ═══════════════════════════════════════════════════════
rect(0,H-40,W,H,C['taskbar'])
rect(2,H-38,40,H-4,'#3a3a3a','#555555')
txt(12,H-30,'W','#cccccc',14,bold=True)
for i in range(5):
    ix=46+i*44
    rect(ix,H-36,ix+38,H-6,'#333333','#555555')
txt(W-88,H-30,'6:17 PM','#dddddd',11)
txt(W-92,H-16,'3/23/2026','#aaaaaa',9)

# ═══════════════════════════════════════════════════════
# SAVE
# ═══════════════════════════════════════════════════════
out='D:/laragon/www/perpus02/Gambar/KrangkaWeb/dashboard/dashboard_admin.png'
img.save(out,'PNG')
print('Saved:',out)
