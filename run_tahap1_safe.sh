cd /home/ilham/Documents/hackathon
set -o pipefail
if [ ! -f 01_prepare_backend.sh ]; then
  echo FILE_01_prepare_backend.sh_TIDAK_ADA
  echo SILAKAN_JALANKAN_BLOK_TAHAP_1_TERLEBIH_DAHLU
  read -r -p "Tekan Enter untuk membuka shell baru"
  exec bash
fi
bash 01_prepare_backend.sh 2>&1 | tee tahap1.log
echo EXIT_CODE=$?
echo LOG_DISIMPAN=/home/ilham/Documents/hackathon/tahap1.log
read -r -p "Tekan Enter untuk membuka shell baru"
exec bash
