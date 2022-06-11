<?php

namespace StdioTemplate\FileHelper;

use FFMpeg\Coordinate\Dimension;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use FFMpeg\Filters\Video\ResizeFilter;

class FFMpegStdio
{
    protected $ffmpeg;

    public function __construct()
    {
        $this->ffmpeg = FFMpeg::create([
            'ffmpeg.binaries'  => config('ffmpeg.url_ffmpeg'),
            'ffprobe.binaries' => config('ffmpeg.url_ffprobe')
        ]);
    }

    public function openFile(string $movie)
    {
        return $this->ffmpeg->open($movie);
    }

    public function resize($fileOpen, $weight = 320, $height = 240)
    {
        return $fileOpen->filters()
            ->resize(new Dimension($weight, $height), ResizeFilter::RESIZEMODE_INSET, true)
            ->synchronize();
    }

    public function timeCode($time)
    {
        return TimeCode::fromSeconds($time);
    }

    public function makeThumbnailFromVideo($video, $seconds = 1)
    {
        return $video->frame($this->timeCode($seconds));
    }

    public function storeThumbnailFromVideo($moviePath, $imagePath, $weight = 320, $height = 240, $seconds = 1)
    {
        return $this->openFile($moviePath)
            ->resize($weight, $height)
            ->makeThumbnailFromVideo($seconds)
            ->save($imagePath);
    }

    public function clipVideo($moviePath, $timeStart, $timeEnd)
    {
        return $this->openFile($moviePath)->filters()
            ->clip($this->timeCode($timeStart), $this->timeCode($timeEnd));
    }

    public function concatVideo($videoRootPath, array $videoConcat, $fileNewPath)
    {
        return $this->openFile($videoRootPath)
            ->concat($videoConcat)
            ->saveFromSameCodecs($fileNewPath, TRUE);
    }
}
