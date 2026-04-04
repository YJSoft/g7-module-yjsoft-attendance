import { defineConfig } from 'vitest/config';
import path from 'path';
import fs from 'fs';

// 프로젝트 루트를 동적으로 탐색 (artisan 파일 기준)
// G7 코어에서 모듈은 modules/{id}/ 에 위치하므로
// 프로젝트 루트는 2단계 상위 (../../) 입니다.
// process.cwd()를 우선 사용: 심볼릭 링크 경로에서 실행될 때
// __dirname은 실제 경로로 해석되어 artisan을 찾지 못할 수 있음.
function findProjectRoot(startDir: string): string {
    let dir = startDir;
    while (dir !== path.dirname(dir)) {
        if (fs.existsSync(path.join(dir, 'artisan'))) return dir;
        dir = path.dirname(dir);
    }
    return path.resolve(startDir, '../../'); // fallback
}

const projectRoot = findProjectRoot(process.cwd());

export default defineConfig({
    test: {
        globals: true,
        environment: 'node',
        include: ['resources/js/**/*.{test,spec}.{ts,tsx}'],
        exclude: ['node_modules/', 'dist/'],
    },
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
            '@core': path.resolve(projectRoot, 'resources/js/core'),
        },
    },
});
