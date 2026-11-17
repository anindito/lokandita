// Pekerja latar Monaco (SPEC.md §16: CSP ketat). Bawaannya Monaco membuat
// pekerja dari URL blob:, yang ditolak script-src kita. Berkas ini disajikan
// dari host sendiri dan memuat kode pekerja dari host sendiri, sehingga CSP
// tidak perlu mengizinkan blob:.
self.MonacoEnvironment = { baseUrl: self.location.origin + '/monaco/' };
importScripts(self.location.origin + '/monaco/vs/base/worker/workerMain.js');
