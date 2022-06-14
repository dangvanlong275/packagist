<?php

namespace StdioTemplate\FileHelper;

use FFMpeg\Coordinate\Dimension;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\Filters\Video\ResizeFilter;
use FFMpeg\Filters\Video\VideoFilters;
use Illuminate\Http\Response;
use ProtoneMedia\LaravelFFMpeg\MediaOpener;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class FFMpegStdio
{
    /**
     * Open file
     * @param string videoPath  đường dẫn tới video
     * @param string fromDisk   nơi lưu trữ file video
     */
    public function openFileDisk($videoPath, string $fromDisk = null)
    {
        if (isset($fromDisk) || $this->checkDisk($fromDisk))
            return FFMpeg::fromDisk($fromDisk)->open($videoPath);

        return FFMpeg::openUrl($videoPath);
    }

    /**
     * Check disk exists
     *
     * @param string disk
     */
    public function checkDisk($disk)
    {
        $disks = array_keys(config('filesystems.disks'));
        return in_array($disk, $disks);
    }
    /**
     * @param mixed format WMV(), X264(), WebM()
     * @param string endFile tên của file được lưu sau khi xuất
     * @param string toDisk nơi lưu file sau khi xuất ra
     */
    public function exportFormatFile(MediaOpener $video, string $endFile, $format, string $toDisk = 'local')
    {
        if (!$this->checkDisk($toDisk))
            throw new \Exception('Disk invalid!', Response::HTTP_BAD_REQUEST);

        return $video->export()
            ->toDisk($toDisk)
            ->inFormat($format)
            ->save($endFile);
    }

    /**
     * @param endFile tên của file được lưu sau khi xuất
     * @param toDisk nơi lưu file sau khi xuất ra
     */
    public function exportNotFormatFile(MediaOpener $video, string $endFile, string $toDisk = 'local')
    {
        if (!$this->checkDisk($toDisk))
            throw new \Exception('Disk invalid!', Response::HTTP_BAD_REQUEST);

        return $video->export()
            ->toDisk($toDisk)
            ->save($endFile);
    }

    /**
     * @param endFile tên của file được lưu sau khi xuất
     * @param toDisk nơi lưu file sau khi xuất ra
     */
    public function exportWithoutTrancoding(MediaOpener $video, string $endFile, string $toDisk = 'local')
    {
        if (!$this->checkDisk($toDisk))
            throw new \Exception('Disk invalid!', Response::HTTP_BAD_REQUEST);

        return $video->export()
            ->concatWithoutTranscoding()
            ->toDisk($toDisk)
            ->save($endFile);
    }

    /**
     * Handle kích thước khung hình
     */
    public function getDismension(array $dimension = [])
    {
        $weight = $dimension['weight'] ?? 320;
        $height = $dimension['height'] ?? 240;

        return compact('weight', 'height');
    }

    public function timeCode($time)
    {
        return TimeCode::fromSeconds($time);
    }

    /**
     *Điều chỉnh kích thước video
     *@param mixed video    : file video sau khi open
     *@param array dimension: Kích thước của khung hình muốn chỉnh sửa
     */
    public function resize(MediaOpener $video, array $dimension = [])
    {
        $dimension = $this->getDismension($dimension);

        $video->addFilter(function (VideoFilters $filters) use ($dimension) {
            $filters->resize(new Dimension($dimension['weight'], $dimension['height']), ResizeFilter::RESIZEMODE_INSET, true)->synchronize();
        });

        return $video;
    }

    /**
     * Cắt video
     * @param mixed video       file video sau khi open
     * @param string videoNew   tên file video mới sau khi resize
     * @param array dismension  kích thước file video mới sau khi resize
     * @param string disk       nơi lưu file video mới
     */
    public function storeVideoResize(MediaOpener $video, string $videoNew, $format, array $dimension = [], string $disk = 'local')
    {
        $video = $this->resize($video, $dimension);

        return $this->exportFormatFile($video, $videoNew, $format, $disk);
    }

    /**
     * Cắt hình ảnh từ video
     * @param mixed video   file video sau khi open
     * @param string image  tên file sau khi export
     * @param string disk   nơi lưu image khi export
     * @param float seconds cắt hình ảnh tại giây bao nhiêu
     */
    public function storeThumbnailFromVideo(MediaOpener $video, $image, $disk = 'local', $seconds = 1)
    {
        $video = $video->getFrameFromSeconds($seconds);

        return $this->exportNotFormatFile($video, $image, $disk);
    }

    /**
     * Cắt video
     * @param string video      video đã open
     * @param string videoNew   tên video sau khi export
     * @param float timeStart   số giây bắt đầu cắt video
     * @param float duration    khoảng thời gian được cắt tính từ thời gian bắt đầu (senconds)
     * @param string disk       nơi lưu video khi export
     */
    public function cutVideo(MediaOpener $video, $videoNew, $timeStart, $duration, $format, $disk = 'local')
    {
        $video->addFilter(function (VideoFilters $filters) use ($timeStart, $duration) {
            $this->clipVideo($filters, $timeStart, $duration);
        });

        return $this->exportFormatFile($video, $videoNew, $format, $disk);
    }

    /**
     * @param mixed video     file video opened instanceof MediaOpener
     * @param float timeStart số giây bắt đầu cắt
     * @param float duration  khoảng thời gian được cắt tính từ thời gian bắt đầu
     */
    public function clipVideo(VideoFilters $video, float $timeStart, float $duration)
    {
        $video->clip($this->timeCode($timeStart), $this->timeCode($duration));

        return $video;
    }

    /**
     * Nối video
     * @param string video          danh sách video đã open (các video phải cùng khung hình)
     * @param float timeStart       số giây bắt đầu cắt
     * @param float duration        khoảng thời gian được cắt tính từ thời gian bắt đầu
     */
    public function concatVideo(MediaOpener $video, string $videoNew, string $disk = 'local')
    {
        return $this->exportWithoutTrancoding($video, $videoNew, $disk);
    }
}
