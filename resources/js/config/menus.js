export const menuConfig = {
  admin: [
    {
      type: 'section',
      title: 'Kelola Data',
      items: [
        { label: 'Kelola Users', to: '/admin/users', icon: 'user-group' },
        { label: 'Kelola Siswa', to: '/admin/siswa', icon: 'users' },
        { label: 'Kelola Jurusan', to: '/admin/jurusan', icon: 'bookmark' },
        { label: 'Kelola Guru', to: '/admin/guru', icon: 'user-tie' },
        { label: 'Kelola Kelas', to: '/admin/kelas', icon: 'home' },
        { label: 'Kelola Wali Kelas', to: '/admin/wali-kelas', icon: 'user-check' },
        { label: 'Kelola Mapel', to: '/admin/mata-pelajaran', icon: 'book' },
        { label: 'Kelola Ekskul', to: '/admin/ekstrakurikuler', icon: 'star' },
        { label: 'Kelola P5', to: '/admin/p5', icon: 'lightbulb' },
        { label: 'Kelola PKL', to: '/admin/pkl', icon: 'briefcase' },
        { label: 'Kelola UKK', to: '/admin/ukk', icon: 'clipboard-check' }
      ]
    },
    {
      type: 'section',
      title: 'Pengaturan',
      items: [
        { label: 'Tahun Ajaran', to: '/admin/tahun-ajaran', icon: 'calendar' }
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
        { label: 'Nilai P5', to: '/guru/nilai-p5', icon: 'lightbulb' }
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
        { label: 'Nilai Ekstrakurikuler', to: '/wali-kelas/nilai-ekstrakurikuler', icon: 'star' },
        { label: 'Nilai P5', to: '/wali-kelas/nilai-p5', icon: 'lightbulb' }
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
      type: 'section',
      title: 'Wali Kelas',
      items: [
        { label: 'Nilai PKL', to: '/wali-kelas/nilai-pkl', icon: 'briefcase' },
        { label: 'Ketidakhadiran', to: '/wali-kelas/ketidakhadiran', icon: 'x-circle' }
      ]
    },
    {
      type: 'dropdown',
      title: 'Cetak Rapor',
      icon: 'printer',
      routes: ['/wali-kelas/cetak-rapor'],
      items: [
        { label: 'Rapor Belajar', to: '/wali-kelas/cetak-rapor/belajar', icon: 'document-text' },
        { label: 'Rapor P5', to: '/wali-kelas/cetak-rapor/p5', icon: 'document-report' },
        { label: 'Legger', to: '/wali-kelas/cetak-rapor/legger', icon: 'table' },
        { label: 'Profil Siswa', to: '/wali-kelas/cetak-rapor/profil-siswa', icon: 'user-circle' }
      ]
    }
  ],

  kepala_sekolah: [
    {
      type: 'dropdown',
      title: 'Approve Rapor',
      icon: 'shield-check',
      routes: ['/kepala-sekolah/rapor-approval'],
      items: [
        { label: 'Rapor Belajar', to: '/kepala-sekolah/rapor-approval/belajar', icon: 'document-text' },
        { label: 'Rapor P5', to: '/kepala-sekolah/rapor-approval/p5', icon: 'lightbulb' }
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