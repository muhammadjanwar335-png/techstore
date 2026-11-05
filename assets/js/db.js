/**
 * TECHSTORE - LocalStorage Database & State Management Engine
 * Menangani seluruh persistensi data produk, kategori, keranjang, transaksi, pembayaran, dan autentikasi.
 * 100% Client-side tanpa ketergantungan PHP maupun MySQL.
 */

const TechDB = {
    KEYS: {
        INITIALIZED: 'techstore_initialized',
        USERS: 'techstore_users',
        CURRENT_USER: 'techstore_current_user',
        CATEGORIES: 'techstore_categories',
        PRODUCTS: 'techstore_products',
        CART: 'techstore_cart',
        TRANSACTIONS: 'techstore_transactions',
        PAYMENTS: 'techstore_payments',
        INVOICES: 'techstore_invoices'
    },

    // Inisialisasi Database Default jika belum ada di LocalStorage
    init() {
        if (!localStorage.getItem(this.KEYS.INITIALIZED)) {
            this.seedData();
        }
    },

    // Reset ke data awal demo
    reset() {
        localStorage.clear();
        this.seedData();
        window.location.reload();
    },

    seedData() {
        // 1. Akun Demo Sesuai Spesifikasi Prompt
        const users = [
            {
                id: 1,
                name: 'Administrator TechStore',
                email: 'admin@techstore.com',
                password: 'admin123',
                role: 'admin',
                phone: '081234567890',
                address: 'Gedung Cyber Tech Lt. 5, Jl. Rasuna Said No. 45, Jakarta Selatan',
                createdAt: '2026-10-01 08:00:00'
            },
            {
                id: 2,
                name: 'Dimas Pratama',
                email: 'customer@techstore.com',
                password: 'customer123',
                role: 'customer',
                phone: '085712345678',
                address: 'Jl. Mawar Merah No. 12, RT 03/RW 05, Kebayoran Baru, Jakarta Selatan',
                createdAt: '2026-10-15 10:30:00'
            },
            {
                id: 3,
                name: 'Siti Rahmawati',
                email: 'siti@techstore.com',
                password: 'customer123',
                role: 'customer',
                phone: '081398765432',
                address: 'Jl. Anggrek Nelimurni No. 88, Slipi, Jakarta Barat',
                createdAt: '2026-10-20 14:15:00'
            }
        ];

        // 2. Minimal 8 Kategori Sesuai Spesifikasi Bagian D
        const categories = [
            { id: 1, name: 'Keyboard', slug: 'keyboard', icon: 'bi-keyboard', description: 'Keyboard mekanikal, gaming RGB, ergonomis dan wireless kantor' },
            { id: 2, name: 'Mouse', slug: 'mouse', icon: 'bi-mouse', description: 'Mouse gaming presisi tinggi, wireless silent click, dan ergonomis vertikal' },
            { id: 3, name: 'Headset', slug: 'headset', icon: 'bi-headphones', description: 'Headset gaming surround 7.1, TWS noise cancelling, dan studio headphone' },
            { id: 4, name: 'Monitor', slug: 'monitor', icon: 'bi-display', description: 'Monitor gaming IPS refresh rate tinggi, ultrawide, dan resolusi 4K profesional' },
            { id: 5, name: 'Webcam', slug: 'webcam', icon: 'bi-camera-video', description: 'Webcam streaming Full HD 1080p 60fps, 2K/4K ring light untuk meeting & live' },
            { id: 6, name: 'Flashdisk', slug: 'flashdisk', icon: 'bi-usb-drive', description: 'Flashdisk USB 3.2 berkecepatan tinggi, OTG dual type-C dan logam tahan air' },
            { id: 7, name: 'SSD dan Harddisk', slug: 'ssd-dan-harddisk', icon: 'bi-device-hdd', description: 'NVMe PCIe Gen 4, SSD Eksternal Portable anti guncang, dan Harddisk Kapasitas Besar' },
            { id: 8, name: 'Accessories Laptop', slug: 'accessories-laptop', icon: 'bi-laptop', description: 'Cooling pad RGB, USB-C Multiport Docking Hub, Aluminium Stand, dan Sleeve Case' }
        ];

        // 3. 24 Data Produk Contoh Lengkap dengan Spek, Harga Nyata, dan Stok
        const products = [
            // Keyboard
            {
                id: 1, categoryId: 1, name: 'Keychron K2 V2 Wireless Mechanical Keyboard', slug: 'keychron-k2-v2',
                price: 1450000, stock: 15, weight: 850, status: 'active',
                image: 'https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=600&auto=format&fit=crop&q=80',
                description: 'Mechanical keyboard 75% compact layout dengan konektivitas Bluetooth 5.1 dan kabel Type-C. Dilengkapi Gateron G Pro Brown switch, RGB backlight 18 mode, baterai 4000mAh tahan hingga 240 jam, kompatibel Mac & Windows.'
            },
            {
                id: 2, categoryId: 1, name: 'Logitech G Pro X TKL Lightspeed Gaming Keyboard', slug: 'logitech-g-pro-x-tkl',
                price: 2350000, stock: 8, weight: 900, status: 'active',
                image: 'https://images.unsplash.com/photo-1618384887929-16ec33fab9ef?w=600&auto=format&fit=crop&q=80',
                description: 'Keyboard gaming tenkeyless kelas turnamen dengan teknologi nirkabel Lightspeed ultra-cepat 1ms. Switch GX Blue Clicky yang tactile dan akurat, LIGHTSYNC RGB, dan kontrol media terdedikasi.'
            },
            {
                id: 3, categoryId: 1, name: 'Rexus Daxa M84 Pro Gasket Mount Hot-swappable', slug: 'rexus-daxa-m84-pro',
                price: 899000, stock: 3, weight: 950, status: 'active',
                image: 'https://images.unsplash.com/photo-1595225476474-87563907a212?w=600&auto=format&fit=crop&q=80',
                description: 'Keyboard mekanikal premium dengan struktur Gasket Mount empuk, fitur hot-swappable 5-pin universal, 3 koneksi (2.4GHz, BT 5.0, Kabel), keycaps PBT doubleshot Cherry profile, dan peredam silikon ganda.'
            },

            // Mouse
            {
                id: 4, categoryId: 2, name: 'Logitech MX Master 3S Performance Wireless Mouse', slug: 'logitech-mx-master-3s',
                price: 1699000, stock: 12, weight: 350, status: 'active',
                image: 'https://images.unsplash.com/photo-1615663245857-ac93bb7c39e7?w=600&auto=format&fit=crop&q=80',
                description: 'Mouse produktivitas tercanggih dengan sensor 8000 DPI Darkfield yang dapat bekerja di atas kaca. Dilengkapi MagSpeed electromagnetic scroll super cepat, tombol Silent Clicks hening 90%, dan thumb scroll wheel.'
            },
            {
                id: 5, categoryId: 2, name: 'Razer DeathAdder V3 Pro Wireless Ultra-lightweight', slug: 'razer-deathadder-v3-pro',
                price: 2150000, stock: 7, weight: 300, status: 'active',
                image: 'https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?w=600&auto=format&fit=crop&q=80',
                description: 'Mouse gaming esports ergonomis paling legendaris dengan bobot ultra-ringan hanya 63 gram. Menggunakan sensor Focus Pro 30K Optical, Optical Mouse Switches Gen-3 dengan ketahanan 90 juta klik, dan baterai hingga 90 jam.'
            },
            {
                id: 6, categoryId: 2, name: 'Fantech Helios II Pro XD3 V3 Wireless Gaming Mouse', slug: 'fantech-helios-ii-pro',
                price: 679000, stock: 4, weight: 250, status: 'active',
                image: 'https://images.unsplash.com/photo-1626785774573-4b799315345d?w=600&auto=format&fit=crop&q=80',
                description: 'Mouse gaming simetris dengan sensor Pixart PAW3395 26.000 DPI, TTC Gold optical encoder, polling rate hingga 4000Hz, baterai hemat daya, serta PTFE skates murni meluncur sangat mulus.'
            },

            // Headset
            {
                id: 7, categoryId: 3, name: 'SteelSeries Arctis Nova 7 Wireless Gaming Headset', slug: 'steelseries-arctis-nova-7',
                price: 2890000, stock: 6, weight: 650, status: 'active',
                image: 'https://images.unsplash.com/photo-1546435770-a3e426bf472b?w=600&auto=format&fit=crop&q=80',
                description: 'Headset gaming nirkabel dengan sistem audio Nova Acoustic System dan High Fidelity Drivers. Mendukung koneksi ganda simultan 2.4GHz dan Bluetooth, mikrofon ClearCast Gen 2 bertenaga AI noise cancelling.'
            },
            {
                id: 8, categoryId: 3, name: 'HyperX Cloud III Gaming Headset 7.1 DTS:X', slug: 'hyperx-cloud-iii',
                price: 1399000, stock: 14, weight: 700, status: 'active',
                image: 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=600&auto=format&fit=crop&q=80',
                description: 'Pembaruan dari headset legendaris Cloud II dengan bantalan memory foam ekstra lembut, driver 53mm yang di-tune ulang untuk kejernihan suara maksimal, mikrofon 10mm ultra-jernih, dan bodi aluminium tangguh.'
            },
            {
                id: 9, categoryId: 3, name: 'Sony WH-1000XM5 Wireless Noise Cancelling Headphone', slug: 'sony-wh-1000xm5',
                price: 4699000, stock: 5, weight: 800, status: 'active',
                image: 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=600&auto=format&fit=crop&q=80',
                description: 'Headphone premium peredam bising terbaik industri dengan prosesor V1 & QN1 ganda. Menghadirkan audio Hi-Res nirkabel LDAC, 8 mikrofon beamforming untuk panggilan telepon kristal jernih, dan baterai 30 jam fast charging.'
            },

            // Monitor
            {
                id: 10, categoryId: 4, name: 'LG UltraGear 27GP850-B 27 Inch Nano IPS QHD 180Hz', slug: 'lg-ultragear-27gp850-b',
                price: 4950000, stock: 4, weight: 7500, status: 'active',
                image: 'https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?w=600&auto=format&fit=crop&q=80',
                description: 'Monitor gaming 27 inci resolusi 2K QHD (2560x1440) dengan panel Nano IPS 1ms GtG, refresh rate 180Hz (O/C), color gamut DCI-P3 98%, HDR400, dan kompatibel NVIDIA G-Sync serta AMD FreeSync Premium.'
            },
            {
                id: 11, categoryId: 4, name: 'Xiaomi Curved Gaming Monitor 30 Inch WFHD 200Hz', slug: 'xiaomi-curved-gaming-monitor-30',
                price: 2899000, stock: 3, weight: 6800, status: 'active',
                image: 'https://images.unsplash.com/photo-1585792180666-f7347c490ee2?w=600&auto=format&fit=crop&q=80',
                description: 'Monitor lengkung ultrawide 21:9 resolusi 2560x1080 dengan kelengkungan 1800R imersif, refresh rate 200Hz cepat, 99% sRGB color gamut, teknologi AMD FreeSync Premium, dan bezel super tipis.'
            },
            {
                id: 12, categoryId: 4, name: 'ASUS ProArt Display PA248QV 24.1 Inch 16:10 IPS', slug: 'asus-proart-display-pa248qv',
                price: 3450000, stock: 9, weight: 6200, status: 'active',
                image: 'https://images.unsplash.com/photo-1547082299-de196ea013d6?w=600&auto=format&fit=crop&q=80',
                description: 'Monitor profesional kreator konten dengan rasio 16:10 resolusi WUXGA (1920x1200), akurasi warna Delta E < 2 terkalibrasi pabrik Calman Verified, 100% sRGB dan 100% Rec. 709, stand ergonomis putar 90 derajat.'
            },

            // Webcam
            {
                id: 13, categoryId: 5, name: 'Logitech Brio 500 Full HD 1080p 60fps Auto-Framing', slug: 'logitech-brio-500',
                price: 1799000, stock: 11, weight: 350, status: 'active',
                image: 'https://images.unsplash.com/photo-1587826080692-f439cd0b70da?w=600&auto=format&fit=crop&q=80',
                description: 'Webcam modern dengan fitur Show Mode untuk memiringkan kamera ke meja presentasi, koreksi cahaya RightLight 4 otomatis, mik noise reduction ganda, dan penutup privasi fisik terintegrasi.'
            },
            {
                id: 14, categoryId: 5, name: 'Razer Kiyo Pro Streaming Webcam with Light Sensor', slug: 'razer-kiyo-pro',
                price: 1899000, stock: 5, weight: 450, status: 'active',
                image: 'https://images.unsplash.com/photo-1588702547919-26089e690ecc?w=600&auto=format&fit=crop&q=80',
                description: 'Webcam streaming kelas pro dengan sensor cahaya adaptif STARVIS Type 1/2.8 ultra-sensitif dalam kondisi redup, resolusi uncompressed Full HD 1080p 60fps, lensa sudut lebar dengan FOV yang dapat disesuaikan.'
            },
            {
                id: 15, categoryId: 5, name: 'Anker PowerConf C200 2K Webcam Ringan & Ringkas', slug: 'anker-powerconf-c200',
                price: 899000, stock: 2, weight: 300, status: 'active',
                image: 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=600&auto=format&fit=crop&q=80',
                description: 'Webcam 2K QHD jernih dengan aperture f/2.0 besar, autofokus cepat 0.5 detik, 2 mikrofon bertenaga AI pengurang bising sekitar, dan cover privasi geser.'
            },

            // Flashdisk
            {
                id: 16, categoryId: 6, name: 'SanDisk Ultra Dual Drive Luxe USB Type-C 128GB', slug: 'sandisk-ultra-dual-drive-luxe-128gb',
                price: 245000, stock: 35, weight: 100, status: 'active',
                image: 'https://images.unsplash.com/photo-1628191011993-9c8e87834bc6?w=600&auto=format&fit=crop&q=80',
                description: 'Flashdisk berbahan logam murni all-metal 2-in-1 dengan konektor reversibel USB Type-C dan Type-A tradisional. Kecepatan baca super cepat hingga 150MB/s untuk transfer file instan antara smartphone dan PC.'
            },
            {
                id: 17, categoryId: 6, name: 'Kingston DataTraveler Max 256GB USB 3.2 Gen 2', slug: 'kingston-datatraveler-max-256gb',
                price: 675000, stock: 18, weight: 120, status: 'active',
                image: 'https://images.unsplash.com/photo-1597872200969-2b65d56bd16b?w=600&auto=format&fit=crop&q=80',
                description: 'Flashdisk performa tingkat tertinggi dengan standar USB 3.2 Gen 2 terkini, menyajikan kecepatan baca hingga 1000MB/s dan tulis 900MB/s setara SSD eksternal dalam ukuran saku.'
            },
            {
                id: 18, categoryId: 6, name: 'Samsung BAR Plus 64GB Titan Gray USB 3.1 300MB/s', slug: 'samsung-bar-plus-64gb',
                price: 175000, stock: 25, weight: 100, status: 'active',
                image: 'https://images.unsplash.com/photo-1563770660941-20978e870e26?w=600&auto=format&fit=crop&q=80',
                description: 'Flashdisk dengan bodi unibody logam tangguh tahan air, benturan, magnet, suhu ekstrem, dan sinar-X. Kecepatan transfer hingga 300MB/s dengan desain gantungan kunci ergonomis modern.'
            },

            // Storage SSD & HDD
            {
                id: 19, categoryId: 7, name: 'Samsung 980 PRO NVMe M.2 SSD 1TB PCIe 4.0', slug: 'samsung-980-pro-nvme-1tb',
                price: 1750000, stock: 10, weight: 200, status: 'active',
                image: 'https://images.unsplash.com/photo-1591488320449-011701bb6704?w=600&auto=format&fit=crop&q=80',
                description: 'SSD internal performa monster dengan kecepatan baca sekuensial hingga 7000MB/s dan tulis 5000MB/s. Dilengkapi pengontrol berlapis nikel dan algoritma termal cerdas untuk menjaga kestabilan gaming & editing berat.'
            },
            {
                id: 20, categoryId: 7, name: 'SanDisk Extreme Portable SSD V2 1TB USB 3.2 Gen 2', slug: 'sandisk-extreme-portable-ssd-v2-1tb',
                price: 1699000, stock: 8, weight: 300, status: 'active',
                image: 'https://images.unsplash.com/photo-1544652478-6653e09f18a2?w=600&auto=format&fit=crop&q=80',
                description: 'SSD eksternal portabel tangguh dengan sertifikasi IP55 tahan air dan debu serta tahan jatuh hingga 2 meter. Kecepatan baca 1050MB/s dan tulis 1000MB/s dengan loop karabiner praktis dibawa bepergian.'
            },
            {
                id: 21, categoryId: 7, name: 'Seagate One Touch External HDD 2TB USB 3.0', slug: 'seagate-one-touch-external-hdd-2tb',
                price: 1120000, stock: 15, weight: 450, status: 'active',
                image: 'https://images.unsplash.com/photo-1531492746076-161ca9bcad58?w=600&auto=format&fit=crop&q=80',
                description: 'Harddisk eksternal berkapasitas besar 2TB dengan casing aluminium bertekstur elegan. Mendukung backup otomatis satu klik, password encryption hardware 256-bit AES, dan kompatibel plug-and-play Windows & Mac.'
            },

            // Accessories Laptop
            {
                id: 22, categoryId: 8, name: 'Baseus Metal Gleam Series 8-in-1 Multifunctional Type-C Hub', slug: 'baseus-metal-gleam-8-in-1',
                price: 485000, stock: 16, weight: 250, status: 'active',
                image: 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=600&auto=format&fit=crop&q=80',
                description: 'Docking adapter USB-C lengkap dengan port HDMI 4K@60Hz, 3x USB 3.0 5Gbps, Type-C Power Delivery 100W fast charge, Gigabit Ethernet RJ45 1000Mbps, serta slot SD/TF card reader bodi aluminium aerospace.'
            },
            {
                id: 23, categoryId: 8, name: 'DeepCool Multi Core X6 Laptop Cooling Pad Dual Fan', slug: 'deepcool-multi-core-x6',
                price: 289000, stock: 2, weight: 1100, status: 'active',
                image: 'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?w=600&auto=format&fit=crop&q=80',
                description: 'Cooling pad laptop dengan 4 kipas pendingin (dua 140mm + dua 100mm) berkecepatan tinggi dengan 4 status kontrol status kipas. Desain geometri kupu-kupu hitam futuristik dengan 2 sudut kemiringan ergonomis.'
            },
            {
                id: 24, categoryId: 8, name: 'Orico Aluminium Adjustable Laptop Stand Ergonomic', slug: 'orico-aluminium-laptop-stand',
                price: 235000, stock: 22, weight: 900, status: 'active',
                image: 'https://images.unsplash.com/photo-1527443195645-1133f7f28990?w=600&auto=format&fit=crop&q=80',
                description: 'Stand dudukan laptop berbahan aluminium murni CNC anodized kuat menopang laptop hingga 17 inci. Sudut kemiringan dan ketinggian dapat disesuaikan fleksibel untuk mencegah nyeri leher dan memperlancar sirkulasi udara laptop.'
            }
        ];

        // 4. Data Transaksi Demo Awal (Agar Grafik, Dashboard & Laporan Langsung Berisi Data Nyata)
        const transactions = [
            {
                id: 1,
                transactionCode: 'TRX-20261028-0001',
                invoiceCode: 'INV/20261028/0001',
                userId: 2,
                recipientName: 'Dimas Pratama',
                recipientPhone: '085712345678',
                shippingAddress: 'Jl. Mawar Merah No. 12, RT 03/RW 05, Kebayoran Baru, Jakarta Selatan',
                shippingCourier: 'JNE Regular',
                shippingCost: 25000,
                totalAmount: 3149000,
                grandTotal: 3174000,
                status: 'completed',
                paymentMethod: 'Transfer Bank - BCA',
                paymentStatus: 'paid',
                paymentProof: 'https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?w=600&auto=format&fit=crop&q=80',
                notes: 'Tolong packing kayu dan bubble wrap tebal',
                createdAt: '2026-10-28 09:30:00',
                items: [
                    { productId: 1, productName: 'Keychron K2 V2 Wireless Mechanical Keyboard', price: 1450000, quantity: 1, subtotal: 1450000 },
                    { productId: 4, productName: 'Logitech MX Master 3S Performance Wireless Mouse', price: 1699000, quantity: 1, subtotal: 1699000 }
                ]
            },
            {
                id: 2,
                transactionCode: 'TRX-20261029-0002',
                invoiceCode: 'INV/20261029/0002',
                userId: 3,
                recipientName: 'Siti Rahmawati',
                recipientPhone: '081398765432',
                shippingAddress: 'Jl. Anggrek Nelimurni No. 88, Slipi, Jakarta Barat',
                shippingCourier: 'SiCepat BEST',
                shippingCost: 15000,
                totalAmount: 1750000,
                grandTotal: 1765000,
                status: 'completed',
                paymentMethod: 'QRIS',
                paymentStatus: 'paid',
                paymentProof: null,
                notes: 'Kirim saat jam kerja kantor',
                createdAt: '2026-10-29 14:15:00',
                items: [
                    { productId: 19, productName: 'Samsung 980 PRO NVMe M.2 SSD 1TB PCIe 4.0', price: 1750000, quantity: 1, subtotal: 1750000 }
                ]
            },
            {
                id: 3,
                transactionCode: 'TRX-20261030-0003',
                invoiceCode: 'INV/20261030/0003',
                userId: 2,
                recipientName: 'Dimas Pratama',
                recipientPhone: '085712345678',
                shippingAddress: 'Jl. Mawar Merah No. 12, RT 03/RW 05, Kebayoran Baru, Jakarta Selatan',
                shippingCourier: 'J&T Express',
                shippingCost: 20000,
                totalAmount: 1699000,
                grandTotal: 1719000,
                status: 'shipped',
                paymentMethod: 'GoPay',
                paymentStatus: 'paid',
                paymentProof: null,
                notes: 'Nomor resi mohon diupdate segera',
                createdAt: '2026-10-30 11:20:00',
                items: [
                    { productId: 20, productName: 'SanDisk Extreme Portable SSD V2 1TB USB 3.2 Gen 2', price: 1699000, quantity: 1, subtotal: 1699000 }
                ]
            },
            {
                id: 4,
                transactionCode: 'TRX-20261031-0004',
                invoiceCode: 'INV/20261031/0004',
                userId: 3,
                recipientName: 'Siti Rahmawati',
                recipientPhone: '081398765432',
                shippingAddress: 'Jl. Anggrek Nelimurni No. 88, Slipi, Jakarta Barat',
                shippingCourier: 'JNE Regular',
                shippingCost: 15000,
                totalAmount: 720000,
                grandTotal: 735000,
                status: 'processing',
                paymentMethod: 'Transfer Bank - Mandiri',
                paymentStatus: 'paid',
                paymentProof: 'https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?w=600&auto=format&fit=crop&q=80',
                notes: 'Pilihan warna hitam',
                createdAt: '2026-10-31 08:45:00',
                items: [
                    { productId: 22, productName: 'Baseus Metal Gleam Series 8-in-1 Multifunctional Type-C Hub', price: 485000, quantity: 1, subtotal: 485000 },
                    { productId: 24, productName: 'Orico Aluminium Adjustable Laptop Stand Ergonomic', price: 235000, quantity: 1, subtotal: 235000 }
                ]
            },
            {
                id: 5,
                transactionCode: 'TRX-20261031-0005',
                invoiceCode: 'INV/20261031/0005',
                userId: 2,
                recipientName: 'Dimas Pratama',
                recipientPhone: '085712345678',
                shippingAddress: 'Jl. Mawar Merah No. 12, RT 03/RW 05, Kebayoran Baru, Jakarta Selatan',
                shippingCourier: 'SiCepat Reguler',
                shippingCost: 18000,
                totalAmount: 899000,
                grandTotal: 917000,
                status: 'pending',
                paymentMethod: 'Transfer Bank - BCA',
                paymentStatus: 'pending',
                paymentProof: null,
                notes: 'Simulasi pembayaran transfer bank',
                createdAt: '2026-10-31 10:10:00',
                items: [
                    { productId: 3, productName: 'Rexus Daxa M84 Pro Gasket Mount Hot-swappable', price: 899000, quantity: 1, subtotal: 899000 }
                ]
            }
        ];

        // Simpan ke LocalStorage
        localStorage.setItem(this.KEYS.USERS, JSON.stringify(users));
        localStorage.setItem(this.KEYS.CATEGORIES, JSON.stringify(categories));
        localStorage.setItem(this.KEYS.PRODUCTS, JSON.stringify(products));
        localStorage.setItem(this.KEYS.TRANSACTIONS, JSON.stringify(transactions));
        localStorage.setItem(this.KEYS.CART, JSON.stringify([]));
        localStorage.setItem(this.KEYS.INITIALIZED, 'true');
    },

    // -----------------------------------------------------------------
    // Autentikasi & Pengguna
    // -----------------------------------------------------------------
    getCurrentUser() {
        const u = localStorage.getItem(this.KEYS.CURRENT_USER);
        return u ? JSON.parse(u) : null;
    },

    setCurrentUser(user) {
        localStorage.setItem(this.KEYS.CURRENT_USER, JSON.stringify(user));
    },

    login(email, password) {
        const users = JSON.parse(localStorage.getItem(this.KEYS.USERS) || '[]');
        const user = users.find(u => u.email.toLowerCase() === email.trim().toLowerCase() && u.password === password);
        if (user) {
            this.setCurrentUser(user);
            return { success: true, user };
        }
        return { success: false, message: 'Email atau kata sandi Anda salah!' };
    },

    logout(redirectUrl) {
        localStorage.removeItem(this.KEYS.CURRENT_USER);
        if (redirectUrl) {
            window.location.href = redirectUrl;
        }
    },

    registerCustomer(data) {
        const users = JSON.parse(localStorage.getItem(this.KEYS.USERS) || '[]');
        if (users.find(u => u.email.toLowerCase() === data.email.trim().toLowerCase())) {
            return { success: false, message: 'Email ini sudah terdaftar!' };
        }
        const newUser = {
            id: Date.now(),
            name: data.name,
            email: data.email,
            password: data.password,
            role: 'customer',
            phone: data.phone,
            address: data.address,
            createdAt: new Date().toISOString().replace('T', ' ').substring(0, 19)
        };
        users.push(newUser);
        localStorage.setItem(this.KEYS.USERS, JSON.stringify(users));
        return { success: true, user: newUser };
    },

    updateUserProfile(userId, updatedData) {
        const users = JSON.parse(localStorage.getItem(this.KEYS.USERS) || '[]');
        const index = users.findIndex(u => u.id === userId);
        if (index !== -1) {
            users[index] = { ...users[index], ...updatedData };
            localStorage.setItem(this.KEYS.USERS, JSON.stringify(users));
            // Perbarui current user jika yang login adalah user bersangkutan
            const curr = this.getCurrentUser();
            if (curr && curr.id === userId) {
                this.setCurrentUser(users[index]);
            }
            return true;
        }
        return false;
    },

    requireAuth(requiredRole, redirectPath) {
        const user = this.getCurrentUser();
        if (!user) {
            alert('Silakan login terlebih dahulu untuk mengakses halaman ini.');
            window.location.href = redirectPath || '../auth/login.html';
            return null;
        }
        if (requiredRole && user.role !== requiredRole) {
            alert('Akses ditolak! Anda tidak memiliki izin untuk mengakses halaman ini.');
            window.location.href = user.role === 'admin' ? '../admin/dashboard.html' : '../index.html';
            return null;
        }
        return user;
    },

    // -----------------------------------------------------------------
    // Manajemen Kategori
    // -----------------------------------------------------------------
    getCategories() {
        return JSON.parse(localStorage.getItem(this.KEYS.CATEGORIES) || '[]');
    },

    getCategoryById(id) {
        return this.getCategories().find(c => c.id === parseInt(id));
    },

    addCategory(cat) {
        const categories = this.getCategories();
        const newCat = {
            id: Date.now(),
            name: cat.name,
            slug: cat.name.toLowerCase().replace(/[^a-z0-9]+/g, '-'),
            icon: cat.icon || 'bi-tag',
            description: cat.description || ''
        };
        categories.push(newCat);
        localStorage.setItem(this.KEYS.CATEGORIES, JSON.stringify(categories));
        return newCat;
    },

    updateCategory(id, updatedCat) {
        const categories = this.getCategories();
        const index = categories.findIndex(c => c.id === parseInt(id));
        if (index !== -1) {
            categories[index] = { ...categories[index], ...updatedCat };
            localStorage.setItem(this.KEYS.CATEGORIES, JSON.stringify(categories));
            return true;
        }
        return false;
    },

    deleteCategory(id) {
        const products = this.getProducts();
        const hasProducts = products.some(p => p.categoryId === parseInt(id));
        if (hasProducts) {
            return { success: false, message: 'Kategori tidak dapat dihapus karena masih menampung produk aktif!' };
        }
        const categories = this.getCategories().filter(c => c.id !== parseInt(id));
        localStorage.setItem(this.KEYS.CATEGORIES, JSON.stringify(categories));
        return { success: true };
    },

    // -----------------------------------------------------------------
    // Manajemen Produk
    // -----------------------------------------------------------------
    getProducts() {
        return JSON.parse(localStorage.getItem(this.KEYS.PRODUCTS) || '[]');
    },

    getProductById(id) {
        return this.getProducts().find(p => p.id === parseInt(id));
    },

    addProduct(prod) {
        const products = this.getProducts();
        const newProd = {
            id: Date.now(),
            categoryId: parseInt(prod.categoryId),
            name: prod.name,
            slug: prod.name.toLowerCase().replace(/[^a-z0-9]+/g, '-') + '-' + Math.floor(Math.random() * 1000),
            price: parseFloat(prod.price),
            stock: parseInt(prod.stock),
            weight: parseInt(prod.weight || 500),
            image: prod.image || 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?w=600&auto=format&fit=crop&q=80',
            description: prod.description,
            status: prod.status || 'active'
        };
        products.unshift(newProd);
        localStorage.setItem(this.KEYS.PRODUCTS, JSON.stringify(products));
        return newProd;
    },

    updateProduct(id, updatedData) {
        const products = this.getProducts();
        const index = products.findIndex(p => p.id === parseInt(id));
        if (index !== -1) {
            products[index] = { ...products[index], ...updatedData };
            localStorage.setItem(this.KEYS.PRODUCTS, JSON.stringify(products));
            return true;
        }
        return false;
    },

    deleteProduct(id) {
        const products = this.getProducts().filter(p => p.id !== parseInt(id));
        localStorage.setItem(this.KEYS.PRODUCTS, JSON.stringify(products));
        return true;
    },

    // -----------------------------------------------------------------
    // Keranjang Belanja (Cart)
    // -----------------------------------------------------------------
    getCart() {
        const cart = JSON.parse(localStorage.getItem(this.KEYS.CART) || '[]');
        const products = this.getProducts();
        const categories = this.getCategories();
        return cart.map(item => {
            const product = products.find(p => p.id === item.productId) || {};
            const category = categories.find(c => c.id === product.categoryId) || {};
            return {
                ...item,
                product,
                categoryName: category.name || 'Accessories',
                subtotal: (product.price || 0) * item.quantity
            };
        }).filter(item => item.product.id);
    },

    getCartCount() {
        const cart = JSON.parse(localStorage.getItem(this.KEYS.CART) || '[]');
        return cart.reduce((total, item) => total + item.quantity, 0);
    },

    addToCart(productId, quantity = 1) {
        const pId = parseInt(productId);
        const qty = parseInt(quantity);
        const product = this.getProductById(pId);
        if (!product) return { success: false, message: 'Produk tidak ditemukan!' };
        if (product.stock < qty) return { success: false, message: `Stok tidak mencukupi (Tersisa ${product.stock} unit)` };

        const cart = JSON.parse(localStorage.getItem(this.KEYS.CART) || '[]');
        const existing = cart.find(item => item.productId === pId);
        if (existing) {
            if (existing.quantity + qty > product.stock) {
                existing.quantity = product.stock;
            } else {
                existing.quantity += qty;
            }
        } else {
            cart.push({ productId: pId, quantity: Math.min(qty, product.stock) });
        }
        localStorage.setItem(this.KEYS.CART, JSON.stringify(cart));
        return { success: true, message: `Produk berhasil ditambahkan ke keranjang!` };
    },

    updateCartQuantity(productId, quantity) {
        const pId = parseInt(productId);
        const qty = parseInt(quantity);
        let cart = JSON.parse(localStorage.getItem(this.KEYS.CART) || '[]');
        if (qty <= 0) {
            cart = cart.filter(item => item.productId !== pId);
        } else {
            const product = this.getProductById(pId);
            const item = cart.find(i => i.productId === pId);
            if (item && product) {
                item.quantity = Math.min(qty, product.stock);
            }
        }
        localStorage.setItem(this.KEYS.CART, JSON.stringify(cart));
    },

    removeFromCart(productId) {
        const pId = parseInt(productId);
        const cart = JSON.parse(localStorage.getItem(this.KEYS.CART) || '[]').filter(item => item.productId !== pId);
        localStorage.setItem(this.KEYS.CART, JSON.stringify(cart));
    },

    clearCart() {
        localStorage.setItem(this.KEYS.CART, JSON.stringify([]));
    },

    // -----------------------------------------------------------------
    // Transaksi & Checkout
    // -----------------------------------------------------------------
    getTransactions(filterUserId = null) {
        let transactions = JSON.parse(localStorage.getItem(this.KEYS.TRANSACTIONS) || '[]');
        if (filterUserId) {
            transactions = transactions.filter(t => t.userId === parseInt(filterUserId));
        }
        return transactions.sort((a, b) => new Date(b.createdAt) - new Date(a.createdAt));
    },

    getTransactionByCode(code) {
        return this.getTransactions().find(t => t.transactionCode === code);
    },

    getInvoiceByCode(invoiceCode) {
        return this.getTransactions().find(t => t.invoiceCode === invoiceCode);
    },

    createTransaction(orderData) {
        const user = this.getCurrentUser();
        const cartItems = this.getCart();
        if (cartItems.length === 0) return { success: false, message: 'Keranjang belanja Anda kosong!' };

        // Validasi stok
        for (const item of cartItems) {
            const prod = this.getProductById(item.productId);
            if (!prod || prod.stock < item.quantity) {
                return { success: false, message: `Stok untuk ${prod ? prod.name : 'produk'} tidak mencukupi!` };
            }
        }

        const dateStr = new Date().toISOString().slice(0, 10).replace(/-/g, '');
        const randomCode = Math.random().toString(36).substring(2, 6).toUpperCase();
        const transactionCode = `TRX-${dateStr}-${randomCode}`;
        const invoiceCode = `INV/${dateStr}/${randomCode}`;

        // Kurangi stok produk di LocalStorage
        const products = this.getProducts();
        cartItems.forEach(item => {
            const p = products.find(prod => prod.id === item.productId);
            if (p) p.stock = Math.max(0, p.stock - item.quantity);
        });
        localStorage.setItem(this.KEYS.PRODUCTS, JSON.stringify(products));

        // Format data transaksi baru
        const newTransaction = {
            id: Date.now(),
            transactionCode,
            invoiceCode,
            userId: user ? user.id : 2,
            recipientName: orderData.recipientName,
            recipientPhone: orderData.recipientPhone,
            shippingAddress: orderData.shippingAddress,
            shippingCourier: orderData.shippingCourier || 'JNE Regular',
            shippingCost: parseFloat(orderData.shippingCost || 20000),
            totalAmount: parseFloat(orderData.totalAmount),
            grandTotal: parseFloat(orderData.grandTotal),
            status: 'pending',
            paymentMethod: orderData.paymentMethod,
            paymentStatus: 'pending',
            paymentProof: null,
            notes: orderData.notes || '',
            createdAt: new Date().toISOString().replace('T', ' ').substring(0, 19),
            items: cartItems.map(item => ({
                productId: item.productId,
                productName: item.product.name,
                price: item.product.price,
                quantity: item.quantity,
                subtotal: item.subtotal
            }))
        };

        const transactions = this.getTransactions();
        transactions.unshift(newTransaction);
        localStorage.setItem(this.KEYS.TRANSACTIONS, JSON.stringify(transactions));

        // Kosongkan keranjang
        this.clearCart();

        return { success: true, transactionCode, invoiceCode };
    },

    updateTransactionStatus(id, newStatus) {
        const transactions = this.getTransactions();
        const index = transactions.findIndex(t => t.id === parseInt(id));
        if (index === -1) return false;

        const oldStatus = transactions[index].status;
        const products = this.getProducts();

        // Jika dibatalkan (cancelled), kembalikan stok
        if (newStatus === 'cancelled' && oldStatus !== 'cancelled') {
            transactions[index].items.forEach(it => {
                const prod = products.find(p => p.id === it.productId);
                if (prod) prod.stock += it.quantity;
            });
            localStorage.setItem(this.KEYS.PRODUCTS, JSON.stringify(products));
        }

        // Jika diaktifkan kembali dari cancelled, kurangi stok lagi
        if (oldStatus === 'cancelled' && newStatus !== 'cancelled') {
            transactions[index].items.forEach(it => {
                const prod = products.find(p => p.id === it.productId);
                if (prod) prod.stock = Math.max(0, prod.stock - it.quantity);
            });
            localStorage.setItem(this.KEYS.PRODUCTS, JSON.stringify(products));
        }

        transactions[index].status = newStatus;
        if (newStatus === 'completed') {
            transactions[index].paymentStatus = 'paid';
        }
        localStorage.setItem(this.KEYS.TRANSACTIONS, JSON.stringify(transactions));
        return true;
    },

    verifyPayment(id, paymentStatus, notes, proofImage = null) {
        const transactions = this.getTransactions();
        const index = transactions.findIndex(t => t.id === parseInt(id));
        if (index === -1) return false;

        transactions[index].paymentStatus = paymentStatus;
        if (notes) transactions[index].notes = notes;
        if (proofImage) transactions[index].paymentProof = proofImage;

        if (paymentStatus === 'paid' && transactions[index].status === 'pending') {
            transactions[index].status = 'processing';
        }
        localStorage.setItem(this.KEYS.TRANSACTIONS, JSON.stringify(transactions));
        return true;
    },

    // -----------------------------------------------------------------
    // Helper Format Tampilan
    // -----------------------------------------------------------------
    formatRupiah(number) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(number || 0);
    },

    formatDate(dateStr, withTime = false) {
        if (!dateStr) return '-';
        const d = new Date(dateStr);
        if (isNaN(d.getTime())) return dateStr;
        const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        const day = d.getDate();
        const month = months[d.getMonth()];
        const year = d.getFullYear();
        if (withTime) {
            const hours = String(d.getHours()).padStart(2, '0');
            const minutes = String(d.getMinutes()).padStart(2, '0');
            return `${day} ${month} ${year}, ${hours}:${minutes} WIB`;
        }
        return `${day} ${month} ${year}`;
    },

    getStatusBadge(status) {
        switch ((status || '').toLowerCase()) {
            case 'pending':
                return '<span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Pending</span>';
            case 'processing':
                return '<span class="badge bg-info text-dark"><i class="bi bi-gear-fill me-1"></i>Diproses</span>';
            case 'shipped':
                return '<span class="badge bg-primary"><i class="bi bi-truck me-1"></i>Dikirim</span>';
            case 'completed':
                return '<span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i>Selesai</span>';
            case 'cancelled':
                return '<span class="badge bg-danger"><i class="bi bi-x-circle-fill me-1"></i>Dibatalkan</span>';
            default:
                return `<span class="badge bg-secondary">${status}</span>`;
        }
    },

    getPaymentBadge(status) {
        switch ((status || '').toLowerCase()) {
            case 'paid':
                return '<span class="badge bg-success"><i class="bi bi-patch-check-fill me-1"></i>Lunas (Paid)</span>';
            case 'pending':
                return '<span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>Menunggu (Pending)</span>';
            case 'failed':
                return '<span class="badge bg-danger"><i class="bi bi-x-octagon-fill me-1"></i>Gagal / Ditolak</span>';
            default:
                return `<span class="badge bg-secondary">${status}</span>`;
        }
    },

    // -----------------------------------------------------------------
    // UI Layout Renderers (Navbar, Footer, Admin Sidebar & Topbar)
    // -----------------------------------------------------------------
    renderNavbar(containerId, activePage = '', rootPath = '') {
        const container = document.getElementById(containerId);
        if (!container) return;

        const user = this.getCurrentUser();
        const cartCount = this.getCartCount();
        const categories = this.getCategories();

        let categoryDropdownHtml = categories.slice(0, 8).map(c => `
            <li>
                <a class="dropdown-item py-2 d-flex align-items-center" href="${rootPath}customer/products.html?category=${c.id}">
                    <i class="bi ${c.icon} me-2 text-primary"></i>${c.name}
                </a>
            </li>
        `).join('');

        let authSection = '';
        if (user) {
            if (user.role === 'admin') {
                authSection = `
                    <li class="nav-item dropdown">
                        <a class="btn btn-outline-info btn-sm dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-shield-lock-fill"></i>
                            <span>${user.name} (Admin)</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark shadow-lg">
                            <li><a class="dropdown-item" href="${rootPath}admin/dashboard.html"><i class="bi bi-speedometer2 me-2"></i>Dashboard Admin</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="javascript:void(0)" onclick="TechDB.logout('${rootPath}auth/login.html')"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                `;
            } else {
                authSection = `
                    <li class="nav-item dropdown">
                        <a class="btn btn-outline-light btn-sm dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle fs-6"></i>
                            <span>${user.name}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark shadow-lg">
                            <li><a class="dropdown-item" href="${rootPath}customer/dashboard.html"><i class="bi bi-speedometer me-2"></i>Dashboard Saya</a></li>
                            <li><a class="dropdown-item" href="${rootPath}customer/history.html"><i class="bi bi-bag-check me-2"></i>Riwayat Pesanan</a></li>
                            <li><a class="dropdown-item" href="${rootPath}customer/profile.html"><i class="bi bi-person-gear me-2"></i>Pengaturan Profil</a></li>
                            <li><hr class="dropdown-divider border-secondary"></li>
                            <li><a class="dropdown-item text-danger" href="javascript:void(0)" onclick="TechDB.logout('${rootPath}auth/login.html')"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                `;
            }
        } else {
            authSection = `
                <li class="nav-item d-flex gap-2 ms-lg-2">
                    <a class="btn btn-outline-light btn-sm px-3" href="${rootPath}auth/login.html">
                        <i class="bi bi-box-arrow-in-right me-1"></i>Masuk
                    </a>
                    <a class="btn btn-primary btn-sm px-3" href="${rootPath}auth/register.html">
                        <i class="bi bi-person-plus me-1"></i>Daftar
                    </a>
                </li>
            `;
        }

        container.innerHTML = `
            <header class="navbar-tech sticky-top">
                <nav class="navbar navbar-expand-lg navbar-dark py-2">
                    <div class="container">
                        <a class="navbar-brand navbar-brand-tech" href="${rootPath}index.html">
                            <i class="bi bi-cpu-fill text-primary"></i>TECH<span>STORE</span>
                        </a>

                        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                            <span class="navbar-toggler-icon"></span>
                        </button>

                        <div class="collapse navbar-collapse" id="navbarContent">
                            <form class="d-flex mx-lg-4 my-2 my-lg-0 flex-grow-1" action="${rootPath}customer/products.html" method="GET" style="max-width: 420px;">
                                <div class="input-group">
                                    <input class="form-control form-control-sm bg-dark text-white border-secondary" type="search" name="q" placeholder="Cari keyboard, mouse, SSD...">
                                    <button class="btn btn-primary btn-sm px-3" type="submit">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </form>

                            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center gap-1">
                                <li class="nav-item">
                                    <a class="nav-link nav-tech-link ${activePage === 'home' ? 'active' : ''}" href="${rootPath}index.html">
                                        <i class="bi bi-house-door me-1"></i>Beranda
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link nav-tech-link ${activePage === 'catalog' ? 'active' : ''}" href="${rootPath}customer/products.html">
                                        <i class="bi bi-grid-fill me-1"></i>Katalog
                                    </a>
                                </li>
                                <li class="nav-item dropdown">
                                    <a class="nav-link nav-tech-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-tags me-1"></i>Kategori
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-dark shadow-lg border-secondary">
                                        ${categoryDropdownHtml}
                                        <li><hr class="dropdown-divider border-secondary"></li>
                                        <li><a class="dropdown-item text-primary fw-semibold" href="${rootPath}customer/products.html">Lihat Semua Kategori &rarr;</a></li>
                                    </ul>
                                </li>
                                <li class="nav-item me-lg-2">
                                    <a class="nav-link nav-tech-link position-relative px-3 ${activePage === 'cart' ? 'active' : ''}" href="${rootPath}customer/cart.html" title="Keranjang Belanja">
                                        <i class="bi bi-cart3 fs-5"></i>
                                        <span class="d-lg-none ms-2">Keranjang</span>
                                        ${cartCount > 0 ? `<span class="badge-cart-count">${cartCount}</span>` : ''}
                                    </a>
                                </li>
                                ${authSection}
                            </ul>
                        </div>
                    </div>
                </nav>
            </header>
        `;
    },

    renderFooter(containerId, rootPath = '') {
        const container = document.getElementById(containerId);
        if (!container) return;

        container.innerHTML = `
            <footer class="footer-tech mt-5">
                <div class="container">
                    <div class="row g-4 mb-4">
                        <div class="col-lg-4 col-md-6">
                            <a class="navbar-brand-tech text-white mb-3 d-inline-flex align-items-center" href="${rootPath}index.html">
                                <i class="bi bi-cpu-fill text-primary fs-3"></i>
                                <span class="fs-4 fw-bold">TECH<span class="text-primary">STORE</span></span>
                            </a>
                            <p class="text-secondary small leading-relaxed">
                                TechStore adalah sistem informasi e-commerce peripheral dan accessories komputer terlengkap. Dirancang untuk demonstrasi tugas kuliah dengan LocalStorage interaktif tanpa ketergantungan server database.
                            </p>
                            <div class="d-flex gap-3 text-secondary fs-5 mt-3">
                                <a href="javascript:void(0)" class="text-secondary"><i class="bi bi-instagram"></i></a>
                                <a href="javascript:void(0)" class="text-secondary"><i class="bi bi-youtube"></i></a>
                                <a href="javascript:void(0)" class="text-secondary"><i class="bi bi-facebook"></i></a>
                                <a href="javascript:void(0)" class="text-secondary"><i class="bi bi-github"></i></a>
                            </div>
                        </div>

                        <div class="col-lg-2 col-md-6 col-6">
                            <h5 class="fw-bold text-white mb-3">Navigasi</h5>
                            <ul class="list-unstyled small d-flex flex-column gap-2 mb-0">
                                <li><a href="${rootPath}index.html">Beranda</a></li>
                                <li><a href="${rootPath}customer/products.html">Katalog Produk</a></li>
                                <li><a href="${rootPath}customer/cart.html">Keranjang Belanja</a></li>
                                <li><a href="${rootPath}customer/history.html">Lacak Pesanan</a></li>
                                <li><a href="${rootPath}auth/login.html">Masuk Akun</a></li>
                            </ul>
                        </div>

                        <div class="col-lg-3 col-md-6 col-6">
                            <h5 class="fw-bold text-white mb-3">Kategori Populer</h5>
                            <ul class="list-unstyled small d-flex flex-column gap-2 mb-0">
                                <li><a href="${rootPath}customer/products.html?category=1">Mechanical Keyboard</a></li>
                                <li><a href="${rootPath}customer/products.html?category=2">Gaming Mouse & Silent</a></li>
                                <li><a href="${rootPath}customer/products.html?category=3">Headset & TWS 7.1</a></li>
                                <li><a href="${rootPath}customer/products.html?category=4">Monitor IPS & Ultrawide</a></li>
                                <li><a href="${rootPath}customer/products.html?category=7">NVMe SSD & Eksternal HDD</a></li>
                            </ul>
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <h5 class="fw-bold text-white mb-3">Kontak & Pembayaran</h5>
                            <ul class="list-unstyled small text-secondary d-flex flex-column gap-2 mb-3">
                                <li><i class="bi bi-geo-alt-fill text-primary me-2"></i>Gedung Cyber Tech Lt. 5, Jakarta</li>
                                <li><i class="bi bi-telephone-fill text-primary me-2"></i>(021) 555-8989 / 0812-3456-7890</li>
                                <li><i class="bi bi-envelope-fill text-primary me-2"></i>support@techstore.com</li>
                            </ul>
                            <div class="text-white small fw-bold mb-2">Simulasi Multi Pembayaran:</div>
                            <div class="d-flex flex-wrap gap-2 text-secondary small">
                                <span class="badge bg-secondary">BCA</span>
                                <span class="badge bg-secondary">Mandiri</span>
                                <span class="badge bg-secondary">QRIS</span>
                                <span class="badge bg-secondary">DANA</span>
                                <span class="badge bg-secondary">GoPay</span>
                                <span class="badge bg-secondary">OVO</span>
                                <span class="badge bg-secondary">COD</span>
                            </div>
                        </div>
                    </div>

                    <hr class="border-secondary my-4">

                    <div class="row align-items-center small text-secondary">
                        <div class="col-md-6 text-center text-md-start mb-2 mb-md-0">
                            &copy; ${new Date().getFullYear()} <strong>TECHSTORE</strong>. Tugas Proyek Perkuliahan Full Stack Web Developer.
                        </div>
                        <div class="col-md-6 text-center text-md-end">
                            <button class="btn btn-outline-danger btn-sm py-0 px-2" style="font-size: 11px;" onclick="if(confirm('Reset seluruh data ke demo awal?')){ TechDB.reset(); }">
                                <i class="bi bi-arrow-counterclockwise me-1"></i>Reset Demo Data LocalStorage
                            </button>
                        </div>
                    </div>
                </div>
            </footer>
        `;
    },

    renderAdminSidebar(containerId, activeMenu = '', rootPath = '') {
        const container = document.getElementById(containerId);
        if (!container) return;

        const transactions = this.getTransactions();
        const pendingPaymentsCount = transactions.filter(t => t.paymentStatus === 'pending').length;
        const lowStockCount = this.getProducts().filter(p => p.stock < 5).length;

        container.innerHTML = `
            <aside class="admin-sidebar" id="adminSidebar">
                <div class="admin-sidebar-header">
                    <i class="bi bi-cpu-fill text-primary fs-3"></i>
                    <div>
                        <h5 class="mb-0 fw-bold text-white fs-5">TECH<span class="text-primary">STORE</span></h5>
                        <small class="text-secondary" style="font-size: 0.72rem; letter-spacing: 0.05em;">ADMIN DASHBOARD</small>
                    </div>
                </div>

                <ul class="admin-sidebar-menu">
                    <li class="admin-menu-header">Utama</li>
                    <li>
                        <a href="${rootPath}admin/dashboard.html" class="admin-menu-link ${activeMenu === 'dashboard' ? 'active' : ''}">
                            <i class="bi bi-speedometer2"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>

                    <li class="admin-menu-header">Manajemen Produk</li>
                    <li>
                        <a href="${rootPath}admin/products/index.html" class="admin-menu-link ${activeMenu === 'products' ? 'active' : ''}">
                            <i class="bi bi-box-seam"></i>
                            <span>Data Produk</span>
                        </a>
                    </li>
                    <li>
                        <a href="${rootPath}admin/categories/index.html" class="admin-menu-link ${activeMenu === 'categories' ? 'active' : ''}">
                            <i class="bi bi-tags"></i>
                            <span>Kategori Produk</span>
                        </a>
                    </li>
                    <li>
                        <a href="${rootPath}admin/products/index.html?filter=low_stock" class="admin-menu-link ${activeMenu === 'low_stock' ? 'active' : ''}">
                            <i class="bi bi-exclamation-triangle"></i>
                            <span>Stok Menipis</span>
                            ${lowStockCount > 0 ? `<span class="badge bg-danger ms-auto rounded-pill">${lowStockCount}</span>` : ''}
                        </a>
                    </li>

                    <li class="admin-menu-header">Transaksi & Pelanggan</li>
                    <li>
                        <a href="${rootPath}admin/transactions/index.html" class="admin-menu-link ${activeMenu === 'transactions' ? 'active' : ''}">
                            <i class="bi bi-receipt-cutoff"></i>
                            <span>Semua Transaksi</span>
                        </a>
                    </li>
                    <li>
                        <a href="${rootPath}admin/payments/index.html" class="admin-menu-link ${activeMenu === 'payments' ? 'active' : ''}">
                            <i class="bi bi-credit-card-2-front"></i>
                            <span>Verifikasi Pembayaran</span>
                            ${pendingPaymentsCount > 0 ? `<span class="badge bg-warning text-dark ms-auto rounded-pill">${pendingPaymentsCount}</span>` : ''}
                        </a>
                    </li>
                    <li>
                        <a href="${rootPath}admin/customers/index.html" class="admin-menu-link ${activeMenu === 'customers' ? 'active' : ''}">
                            <i class="bi bi-people"></i>
                            <span>Data Pelanggan</span>
                        </a>
                    </li>

                    <li class="admin-menu-header">Analitik & Laporan</li>
                    <li>
                        <a href="${rootPath}admin/reports/index.html" class="admin-menu-link ${activeMenu === 'reports' ? 'active' : ''}">
                            <i class="bi bi-graph-up-arrow"></i>
                            <span>Laporan Penjualan</span>
                        </a>
                    </li>

                    <li class="admin-menu-header">Sistem & Toko</li>
                    <li>
                        <a href="${rootPath}index.html" target="_blank" class="admin-menu-link text-info">
                            <i class="bi bi-box-arrow-up-right"></i>
                            <span>Lihat Website Toko</span>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0)" onclick="TechDB.logout('${rootPath}auth/login.html')" class="admin-menu-link text-danger mt-3">
                            <i class="bi bi-box-arrow-left"></i>
                            <span>Keluar (Logout)</span>
                        </a>
                    </li>
                </ul>
            </aside>
        `;
    },

    renderAdminTopbar(containerId, rootPath = '') {
        const container = document.getElementById(containerId);
        if (!container) return;

        const user = this.getCurrentUser() || { name: 'Admin', email: 'admin@techstore.com' };
        const pendingCount = this.getTransactions().filter(t => t.paymentStatus === 'pending').length;

        container.innerHTML = `
            <header class="admin-topbar">
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-sm btn-outline-secondary d-md-none" type="button" onclick="document.getElementById('adminSidebar').classList.toggle('d-block');">
                        <i class="bi bi-list fs-5"></i>
                    </button>
                    <div class="text-secondary small d-none d-sm-block">
                        <i class="bi bi-calendar3 me-1 text-primary"></i> ${this.formatDate(new Date())} |
                        <i class="bi bi-clock me-1 text-primary"></i> <span id="adminLiveClock">--:--</span> WIB
                    </div>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <a href="${rootPath}admin/payments/index.html" class="btn btn-sm btn-light border position-relative" title="Pembayaran Menunggu Verifikasi">
                        <i class="bi bi-bell-fill text-secondary"></i>
                        ${pendingCount > 0 ? `<span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>` : ''}
                    </a>

                    <div class="dropdown">
                        <button class="btn btn-sm btn-light border dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-weight: bold; font-size: 12px;">
                                A
                            </div>
                            <span class="fw-semibold small d-none d-md-inline">${user.name}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                            <li class="px-3 py-2 small text-muted border-bottom">
                                <strong>${user.name}</strong><br>
                                <span>${user.email}</span>
                            </li>
                            <li><a class="dropdown-item py-2" href="${rootPath}admin/dashboard.html"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                            <li><a class="dropdown-item py-2" href="${rootPath}index.html" target="_blank"><i class="bi bi-shop me-2"></i>Kunjungi Toko</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item py-2 text-danger" href="javascript:void(0)" onclick="TechDB.logout('${rootPath}auth/login.html')"><i class="bi bi-box-arrow-left me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                </div>
            </header>
        `;

        function updateClock() {
            const el = document.getElementById('adminLiveClock');
            if (el) {
                const now = new Date();
                el.textContent = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`;
            }
        }
        updateClock();
        setInterval(updateClock, 30000);
    }
};

// Jalankan auto-init saat script dimuat
TechDB.init();
