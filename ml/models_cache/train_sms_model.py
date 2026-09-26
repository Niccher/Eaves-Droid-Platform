#!/usr/bin/env python3
"""
SMS Classifier Bootstrap Trainer
==================================
Downloads the UCI SMS Spam Collection dataset (public domain, ~500 KB),
trains a TF-IDF + Multinomial Naive Bayes pipeline, and saves the artefacts:

    models_cache/sms_vectorizer.joblib
    models_cache/sms_classifier.joblib

Run once from the ML Eaves Droid project root:

    python models_cache/train_sms_model.py

After running, restart the FastAPI service so it picks up the new artefacts.
The ``sms_bert`` detector will automatically switch from the rule-engine
fallback to the trained model on next startup.

Requirements (all already in requirements.txt):
    scikit-learn, joblib, numpy
"""

from __future__ import annotations

import csv
import io
import os
import sys
import zipfile
from pathlib import Path

# ---------------------------------------------------------------------------
# Configuration
# ---------------------------------------------------------------------------
_HERE      = Path(__file__).parent           # models_cache/
_VEC_PATH  = _HERE / "sms_vectorizer.joblib"
_CLF_PATH  = _HERE / "sms_classifier.joblib"

# UCI SMS Spam Collection (hosted on the UCI ML repository)
_DATASET_URL = (
    "https://archive.ics.uci.edu/ml/machine-learning-databases/00228/smsspamcollection.zip"
)

# ---------------------------------------------------------------------------
# Helpers
# ---------------------------------------------------------------------------

def _fetch_dataset() -> list[tuple[int, str]]:
    """
    Download and parse the UCI SMS Spam Collection.
    Returns a list of (label_int, message_text) pairs where
    label_int = 1 for spam, 0 for ham.
    """
    import urllib.request

    print(f"Downloading dataset from:\n  {_DATASET_URL}\n")
    with urllib.request.urlopen(_DATASET_URL, timeout=30) as resp:
        raw = resp.read()

    with zipfile.ZipFile(io.BytesIO(raw)) as zf:
        candidates = [n for n in zf.namelist() if "SMSSpamCollection" in n]
        if not candidates:
            raise FileNotFoundError(
                "SMSSpamCollection not found in zip. "
                f"Archive contains: {zf.namelist()}"
            )
        content = zf.read(candidates[0]).decode("utf-8", errors="replace")

    records: list[tuple[int, str]] = []
    reader = csv.reader(io.StringIO(content), delimiter="\t")
    for row in reader:
        if len(row) < 2:
            continue
        label_str, body = row[0].strip().lower(), row[1].strip()
        label_int = 1 if label_str == "spam" else 0
        records.append((label_int, body))

    spam_count = sum(1 for l, _ in records if l == 1)
    ham_count  = len(records) - spam_count
    print(f"Dataset loaded: {len(records)} messages  ({spam_count} spam / {ham_count} ham)")
    return records


def _preprocess(text: str) -> str:
    """Mirror the preprocessing logic in sms_bert.py."""
    import re
    text = text.lower()
    text = re.sub(r"https?://\S+|www\.\S+", " url_token ", text)
    text = re.sub(r"[^a-z0-9\s]", " ", text)
    return re.sub(r"\s+", " ", text).strip()


def _train(records: list[tuple[int, str]]) -> tuple:
    """Train TF-IDF vectorizer + Multinomial Naive Bayes on the dataset."""
    from sklearn.feature_extraction.text import TfidfVectorizer
    from sklearn.naive_bayes import MultinomialNB
    from sklearn.model_selection import cross_val_score
    import numpy as np

    labels = [r[0] for r in records]
    bodies = [_preprocess(r[1]) for r in records]

    print("Fitting TF-IDF vectorizer ...")
    vectorizer = TfidfVectorizer(
        ngram_range=(1, 2),
        max_features=20_000,
        sublinear_tf=True,
        strip_accents="unicode",
        min_df=2,
    )
    X = vectorizer.fit_transform(bodies)

    print("Training Multinomial Naive Bayes ...")
    clf = MultinomialNB(alpha=0.1)

    print("Running 5-fold cross-validation ...")
    cv_scores = cross_val_score(clf, X, labels, cv=5, scoring="f1")
    print(
        f"Cross-validation F1 scores: {[round(s, 4) for s in cv_scores]}\n"
        f"Mean F1: {np.mean(cv_scores):.4f}  +/- {np.std(cv_scores):.4f}"
    )

    clf.fit(X, labels)
    return vectorizer, clf


def _save(vectorizer, clf) -> None:
    import joblib
    _HERE.mkdir(parents=True, exist_ok=True)
    joblib.dump(vectorizer, _VEC_PATH, compress=3)
    joblib.dump(clf,        _CLF_PATH, compress=3)
    print(f"\nSaved:\n  {_VEC_PATH}\n  {_CLF_PATH}")


def main() -> None:
    if _VEC_PATH.exists() and _CLF_PATH.exists():
        if "--force" not in sys.argv:
            print(
                "Model artefacts already exist:\n"
                f"  {_VEC_PATH}\n  {_CLF_PATH}\n\n"
                "Pass --force to retrain and overwrite."
            )
            sys.exit(0)
        print("--force detected — retraining and overwriting existing artefacts.\n")

    records  = _fetch_dataset()
    vec, clf = _train(records)
    _save(vec, clf)

    print(
        "\nDone. Restart the FastAPI service to activate the trained classifier:\n"
        "  docker compose restart ml-eaves-droid\n"
        "  # or: uvicorn app.main:app --reload --port 9070"
    )


if __name__ == "__main__":
    main()
