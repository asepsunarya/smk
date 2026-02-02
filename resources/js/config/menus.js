export const menuConfig = {
  admin: [
    {
      type: 'section',
      title: 'Kelola Data',
      items: [
        { label: 'Tahun Ajaran', to: '/admin/tahun-ajaran', icon: 'calendar' },
        { label: 'Kelola Guru', to: '/admin/guru', icon: 'user-tie' },
        { label: 'Kelola Jurusan', to: '/admin/jurusan', icon: 'bookmark' },
        { label: 'Kelola Kelas', to: '/admin/kelas', icon: 'home' },
        { label: 'Kelola Siswa', to: '/admin/siswa', icon: 'users' },
        { label: 'Kelola Users', to: '/admin/users', icon: 'user-group' },
        { label: 'Kelola Wali Kelas', to: '/admin/wali-kelas', icon: 'user-check' },
        { label: 'Kelola Mapel', to: '/admin/mata-pelajaran', icon: 'book' },
        { label: 'Kelola Ekskul', to: '/admin/ekstrakurikuler', icon: 'star' },
        { label: 'Kelola PKL', to: '/admin/pkl', icon: 'briefcase' },
        { label: 'Kelola UKK', to: '/admin/ukk-events', icon: 'clipboard-check' }
      ]
    },
    {
      type: 'dropdown',
      title: 'Kelola P5',
      icon: 'lightbulb',
      routes: ['/admin/p5', '/admin/p5/kelompok', '/admin/ukk-events'],
      items: [
        { label: 'Data P5', to: '/admin/p5', icon: 'document-duplicate' },
        { label: 'Kelompok P5', to: '/admin/p5/kelompok', icon: 'user-group' },
      ]
    },
    {
      type: 'dropdown',
      title: 'Cetak Rapor',
      icon: 'printer',
      routes: ['/admin/cetak-rapor'],
      items: [
        { label: 'Rapor Hasil Belajar', to: '/admin/cetak-rapor/hasil-belajar', icon: 'document-text' },
        { label: 'Rapor Hasil P5', to: '/admin/cetak-rapor/p5', icon: 'document-report' },
        { label: 'Nilai UKK', to: '/admin/cetak-rapor/nilai-ukk', icon: 'document-report' },
        { label: 'Legger', to: '/admin/cetak-rapor/legger', icon: 'table' }
      ]
    }
  ],

  guru: [
    {
      type: 'section',
      title: 'Pembelajaran',
      items: [
        { label: 'Capaian Pembelajaran', to: '/guru/capaian-pembelajaran', icon: 'target' },
        { label: 'Kelola Nilai Sumatif', to: '/guru/nilai', icon: 'clipboard-check' },
        { label: 'Nilai Ekstrakurikuler', to: '/guru/nilai-ekstrakurikuler', icon: 'star' },
        { label: 'Nilai P5', to: '/guru/p5', icon: 'lightbulb' },
        { label: 'Nilai UKK', to: '/guru/nilai-ukk', icon: 'clipboard-check', kepalaJurusanOnly: true }
      ]
    }
  ],

  wali_kelas: [
    {
      type: 'section',
      title: 'Pembelajaran',
      items: [
        { label: 'Capaian Pembelajaran', to: '/wali-kelas/capaian-pembelajaran', icon: 'target' },
        { label: 'Kelola Nilai Sumatif', to: '/wali-kelas/nilai-sumatif', icon: 'clipboard-check' },
        { label: 'Nilai Ekstrakurikuler', to: '/wali-kelas/nilai-ekstrakurikuler', icon: 'star' }
      ]
    },
    {
      type: 'section',
      title: 'Wali Kelas',
      items: [
        { label: 'Nilai PKL', to: '/wali-kelas/nilai-pkl', icon: 'briefcase' },
        { label: 'Ketidakhadiran', to: '/wali-kelas/ketidakhadiran', icon: 'x-circle' },
        { label: 'Catatan Wali Kelas', to: '/wali-kelas/catatan-wali-kelas', icon: 'document-text' }
      ]
    },
    {
      type: 'dropdown',
      title: 'Cek Penilaian',
      icon: 'chart-bar',
      routes: ['/wali-kelas/cek-penilaian'],
      items: [
        { label: 'Nilai STS', to: '/wali-kelas/cek-penilaian/sts', icon: 'check' },
        { label: 'Nilai SAS', to: '/wali-kelas/cek-penilaian/sas', icon: 'check' },
        { label: 'Nilai P5', to: '/wali-kelas/cek-penilaian/p5', icon: 'check' }
      ]
    },
    {
      type: 'dropdown',
      title: 'Cetak Rapor',
      icon: 'printer',
      routes: ['/wali-kelas/cetak-rapor'],
      items: [
        { label: 'Rapor Hasil Belajar', to: '/wali-kelas/cetak-rapor/belajar', icon: 'document-text' },
        { label: 'Rapor P5', to: '/wali-kelas/cetak-rapor/p5', icon: 'document-report' },
        { label: 'Legger', to: '/wali-kelas/cetak-rapor/legger', icon: 'table' },
        { label: 'Profil Siswa', to: '/wali-kelas/cetak-rapor/profil-siswa', icon: 'user-circle' }
      ]
    }
  ],

  kepala_sekolah: [
    {
      type: 'section',
      title: 'Menu',
      items: [
        { label: 'Approve Rapor', to: '/kepala-sekolah/rapor-approval/belajar', icon: 'shield-check' }
      ]
    }
  ],

  siswa: [
    {
      type: 'dropdown',
      title: 'Cek Rapor',
      icon: 'document-text',
      routes: ['/siswa/rapor'],
      items: [
        { label: 'Rapor Belajar', to: '/siswa/rapor/belajar', icon: 'document-text' },
        { label: 'Rapor P5', to: '/siswa/rapor/p5', icon: 'lightbulb' }
      ]
    }
  ]
}