import os
import re
import sys
import time
import uuid
import logging
from datetime import datetime
from contextlib import redirect_stderr, redirect_stdout

from fastapi import FastAPI, UploadFile, File, HTTPException
from fastapi.middleware.cors import CORSMiddleware

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

app = FastAPI(title="OCR Service", version="1.0.0")

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

reader = None


def get_reader():
    global reader
    if reader is None:
        logger.info("Loading EasyOCR reader...")
        import easyocr
        with redirect_stderr(open(os.devnull, 'w')), redirect_stdout(open(os.devnull, 'w')):
            reader = easyocr.Reader(['id', 'en'], gpu=False, verbose=False)
        logger.info("EasyOCR loaded successfully")
    return reader


@app.on_event("startup")
async def startup():
    logger.info("Initializing OCR engine...")
    try:
        get_reader()
        logger.info("OCR engine ready")
    except Exception as e:
        logger.error(f"Failed to load EasyOCR: {e}")
        logger.error("OCR will not be available until service restart")


@app.get("/health")
def health():
    return {
        "status": "healthy",
        "reader_loaded": reader is not None,
        "timestamp": datetime.now().isoformat()
    }


@app.post("/ocr")
async def process_ocr(file: UploadFile = File(...)):
    start_time = time.time()

    if not file.filename:
        raise HTTPException(400, "No file provided")

    ext = os.path.splitext(file.filename)[1].lower()
    supported = ['.png', '.jpg', '.jpeg', '.tiff', '.tif', '.bmp', '.pdf']

    if ext not in supported:
        raise HTTPException(400, f"Unsupported file type: {ext}")

    if reader is None:
        raise HTTPException(503, "OCR engine not initialized. Check server logs.")

    temp_dir = "temp"
    os.makedirs(temp_dir, exist_ok=True)
    temp_path = os.path.join(temp_dir, f"{uuid.uuid4()}{ext}")

    try:
        content = await file.read()
        with open(temp_path, "wb") as f:
            f.write(content)

        logger.info(f"Processing: {file.filename} ({len(content)} bytes)")

        with redirect_stdout(open(os.devnull, 'w')):
            results = reader.readtext(temp_path, paragraph=True)

        raw_lines = []
        confidences = []

        if results:
            for bbox, text, conf in results:
                raw_lines.append(text)
                confidences.append(conf)

        raw_text = "\n".join(raw_lines)
        avg_confidence = sum(confidences) / len(confidences) if confidences else 0
        processing_time = round(time.time() - start_time, 2)

        fields = extract_fields(raw_text)

        response = {
            "raw_text": raw_text,
            "confidence_score": round(avg_confidence, 4),
            "fields": fields,
            "processing_time": processing_time,
        }

        logger.info(f"OCR complete in {processing_time}s | confidence={avg_confidence:.4f} | chars={len(raw_text)}")
        return response

    except Exception as e:
        logger.error(f"OCR failed: {str(e)}")
        raise HTTPException(500, f"OCR processing failed: {str(e)}")

    finally:
        if os.path.exists(temp_path):
            os.remove(temp_path)


def extract_fields(text: str) -> dict:
    fields = {
        "student_name": None,
        "student_number": None,
        "university": None,
        "faculty": None,
        "study_program": None,
        "gpa": None,
        "graduation_year": None,
    }

    m = re.search(r'(?:Nama|NAMA|nama)\s*(?:Mahasiswa)?\s*[:.]?\s*([A-Za-z\s]+)', text)
    if m:
        fields["student_name"] = m.group(1).strip()

    m = re.search(r'(?:NIM|nim)\s*[:.]?\s*(\d{8,12})', text)
    if m:
        fields["student_number"] = m.group(1)

    m = re.search(r'INSTITUT\s+TEKNOLOGI\s+NASIONAL', text, re.IGNORECASE)
    if m:
        fields["university"] = m.group(0)

    m = re.search(r'(?:Fakultas|FAKULTAS)\s+(\w+(?:\s+\w+){0,3})', text)
    if m:
        fields["faculty"] = m.group(1).strip()

    m = re.search(r'(?:Program\s+Studi|PROGRAM\s+STUDI|Prodi)\s*[:.]?\s*(\w+(?:\s+\w+){0,3})', text)
    if m:
        fields["study_program"] = m.group(1).strip()

    m = re.search(r'(?:IPK|GPA)\s*[:.]?\s*(\d+[.,]\d+)', text)
    if m:
        fields["gpa"] = float(m.group(1).replace(",", "."))

    years = re.findall(r'\b(20\d{2})\b', text)
    if years:
        for y in years:
            y_int = int(y)
            if 2020 <= y_int <= 2035:
                fields["graduation_year"] = y_int
                break

    return fields


@app.get("/")
def root():
    return {
        "service": "OCR Service",
        "status": "running",
        "endpoints": {
            "/ocr": "POST - OCR processing",
            "/health": "GET - Health check"
        }
    }
