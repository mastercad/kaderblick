<?php

namespace App\Service;

use Exception;
use Google\Client;
use Google\Http\MediaFileUpload;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class GoogleDriveService
{
    private Drive $driveService;

    public function __construct()
    {
        $client = new Client();
        $client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
        $client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
        $client->refreshToken($_ENV['GOOGLE_REFRESH_TOKEN']);

        $this->driveService = new Drive($client);
    }

    private function findFolderByName(string $name, ?string $parentId = null): ?string
    {
        $query = "mimeType='application/vnd.google-apps.folder' and name='" . $name . "' and trashed=false";
        if ($parentId) {
            $query .= " and '" . $parentId . "' in parents";
        } else {
            $query .= " and '" . $_ENV['GOOGLE_FOLDER_ID'] . "' in parents";
        }

        $response = $this->driveService->files->listFiles([
            'q' => $query,
            'spaces' => 'drive',
            'fields' => 'files(id, name)'
        ]);

        if (count($response->getFiles()) > 0) {
            return $response->getFiles()[0]->getId();
        }

        return null;
    }

    public function createGameFolder(string $gameName, bool $force = false): string
    {
        $existingFolderId = $this->findFolderByName($gameName);

        if ($existingFolderId && !$force) {
            return $existingFolderId;
        }

        $fileMetadata = new DriveFile([
            'name' => $gameName,
            'mimeType' => 'application/vnd.google-apps.folder',
            'parents' => [$_ENV['GOOGLE_FOLDER_ID']]
        ]);

        if ($existingFolderId) {
            // Update existing folder - ohne parents
            $updateMetadata = new DriveFile(['name' => $gameName]);
            $this->driveService->files->update($existingFolderId, $updateMetadata, [
                'addParents' => $_ENV['GOOGLE_FOLDER_ID'],
                'removeParents' => 'root'
            ]);

            return $existingFolderId;
        }

        $folder = $this->driveService->files->create($fileMetadata);

        return $folder->getId();
    }

    public function createDeviceFolder(string $parentFolderId, string $deviceName, bool $force = false): string
    {
        $existingFolderId = $this->findFolderByName($deviceName, $parentFolderId);

        if ($existingFolderId && !$force) {
            return $existingFolderId;
        }

        $fileMetadata = new DriveFile([
            'name' => $deviceName,
            'mimeType' => 'application/vnd.google-apps.folder',
            'parents' => [$parentFolderId]
        ]);

        if ($existingFolderId) {
            // Update existing folder - ohne parents
            $updateMetadata = new DriveFile(['name' => $deviceName]);
            $this->driveService->files->update($existingFolderId, $updateMetadata, [
                'addParents' => $parentFolderId,
                'removeParents' => 'root'
            ]);

            return $existingFolderId;
        }

        $folder = $this->driveService->files->create($fileMetadata);

        return $folder->getId();
    }

    public function uploadVideo(UploadedFile $file, string $folderId): string
    {
        $fileMetadata = new DriveFile([
            'name' => $file->getClientOriginalName(),
            'parents' => [$folderId]
        ]);

        try {
            $file = $this->driveService->files->create($fileMetadata, [
                'data' => file_get_contents($file->getPathname()),
                'mimeType' => $file->getMimeType(),
                'uploadType' => 'multipart'
            ]);

            return $file->getId();
        } catch (Exception $e) {
            throw new Exception('Upload failed: ' . $e->getMessage());
        }
    }

    public function chunkedUploadVideo(UploadedFile $file, string $folderId, ?callable $progressCallback = null): string
    {
        $fileMetadata = new DriveFile([
            'name' => $file->getClientOriginalName(),
            'parents' => [$folderId],
        ]);

        $client = $this->driveService->getClient();
        $client->setDefer(true);

        try {
            $request = new \GuzzleHttp\Psr7\Request(
                'POST',
                'https://www.googleapis.com/upload/drive/v3/files',
                ['Content-Type' => 'application/json'],
                json_encode($fileMetadata)
            );

            $media = new MediaFileUpload(
                $client,
                $request,
                $file->getMimeType(),
                '',
                true,
                1024 * 1024
            );

            $media->setFileSize($file->getSize());
            $status = false;
            $uploaded = 0;
            $totalSize = $file->getSize();
            $handle = fopen($file->getPathname(), 'rb');

            while (!$status && !feof($handle)) {
                $chunk = fread($handle, 1024 * 1024);
                $status = $media->nextChunk($chunk);
                $uploaded += strlen($chunk);

                if ($progressCallback) {
                    $progress = ($uploaded / $totalSize) * 100;
                    $progressCallback($progress);
                }
            }

            fclose($handle);
            $client->setDefer(false);

            if ($status) {
                return $status->getId();
            }

            throw new Exception('Upload failed: No file ID received');
        } catch (Exception $e) {
            if (isset($handle) && is_resource($handle)) {
                fclose($handle);
            }
            $client->setDefer(false);
            throw new Exception('Upload failed: ' . $e->getMessage());
        }
    }

    public function getFolderUrl(string $folderId): string
    {
        return "https://drive.google.com/drive/folders/{$folderId}";
    }
}
