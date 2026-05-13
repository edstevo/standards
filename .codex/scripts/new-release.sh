#!/usr/bin/env bash
set -euo pipefail

remote="${REMOTE:-origin}"
branch="${BRANCH:-main}"
release_type="${RELEASE_TYPE:-patch}"
release_version="${RELEASE_VERSION:-}"

cd "$(git rev-parse --show-toplevel)"

if [[ -n "$(git status --porcelain)" ]]; then
    echo "Working tree is not clean. Commit or stash changes before releasing."
    git status --short
    exit 1
fi

current_branch="$(git branch --show-current)"

if [[ "$current_branch" != "$branch" ]]; then
    echo "Releases must be run from $branch. Current branch: $current_branch."
    echo "Set BRANCH=$current_branch to release from this branch intentionally."
    exit 1
fi

git fetch --no-tags "$remote" "$branch:refs/remotes/$remote/$branch"

if ! git merge-base --is-ancestor "$remote/$branch" HEAD; then
    echo "Local $branch is behind or has diverged from $remote/$branch. Pull or rebase before releasing."
    exit 1
fi

if [[ -n "$(git tag --points-at HEAD --list 'v[0-9]*')" ]]; then
    echo "HEAD already has a release tag:"
    git tag --points-at HEAD --list 'v[0-9]*'
    exit 1
fi

if [[ -n "$release_version" ]]; then
    release_version="${release_version#v}"

    if [[ ! "$release_version" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
        echo "RELEASE_VERSION must be a semantic version like 1.2.3 or v1.2.3."
        exit 1
    fi
else
    latest_tag="$(git tag --list 'v[0-9]*' --sort=-v:refname | head -n 1)"

    if [[ -z "$latest_tag" ]]; then
        latest_tag="v0.0.0"
    fi

    if [[ ! "$latest_tag" =~ ^v([0-9]+)\.([0-9]+)\.([0-9]+)$ ]]; then
        echo "Latest release tag is not a simple semantic version: $latest_tag"
        echo "Set RELEASE_VERSION=1.2.3 to release explicitly."
        exit 1
    fi

    major="${BASH_REMATCH[1]}"
    minor="${BASH_REMATCH[2]}"
    patch="${BASH_REMATCH[3]}"

    case "$release_type" in
        major)
            major=$((major + 1))
            minor=0
            patch=0
            ;;
        minor)
            minor=$((minor + 1))
            patch=0
            ;;
        patch)
            patch=$((patch + 1))
            ;;
        *)
            echo "RELEASE_TYPE must be major, minor, or patch."
            echo "Set RELEASE_VERSION=1.2.3 to release an explicit version."
            exit 1
            ;;
    esac

    release_version="${major}.${minor}.${patch}"
fi

tag="v${release_version}"

if git rev-parse -q --verify "refs/tags/$tag" >/dev/null; then
    echo "Release tag already exists: $tag"
    exit 1
fi

if git ls-remote --exit-code --tags "$remote" "refs/tags/$tag" >/dev/null; then
    echo "Release tag already exists on $remote: $tag"
    exit 1
fi

git tag -a "$tag" -m "$tag"
git push "$remote" "$branch"
git push "$remote" "$tag"

echo "Released $tag"
